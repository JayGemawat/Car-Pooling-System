<?php

class PushController
{
    public function subscribe(): void
    {
        AuthMiddleware::require();
        header('Content-Type: application/json');

        $uid  = AuthMiddleware::userId();
        $body = json_decode(file_get_contents('php://input'), true);

        if (!is_array($body)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON']);
            return;
        }

        // CSRF check via JSON body token
        $token = $body['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            echo json_encode(['error' => 'CSRF token mismatch']);
            return;
        }

        $endpoint = trim($body['endpoint']       ?? '');
        $p256dh   = trim($body['keys']['p256dh'] ?? '');
        $auth     = trim($body['keys']['auth']   ?? '');

        if (!$endpoint || !$p256dh || !$auth) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid subscription data']);
            return;
        }

        // Validate endpoint is a proper HTTPS URL
        if (!filter_var($endpoint, FILTER_VALIDATE_URL) || !str_starts_with($endpoint, 'https://')) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid endpoint']);
            return;
        }

        try {
            $pdo = getDB();
            $pdo->prepare("
                INSERT INTO push_subscriptions (uid, endpoint, p256dh, auth_key)
                VALUES (?, ?, ?, ?)
                ON CONFLICT (endpoint) DO UPDATE SET uid = EXCLUDED.uid
            ")->execute([$uid, $endpoint, $p256dh, $auth]);
        } catch (PDOException $e) {
            // Table not yet created — silently ignore, push is non-critical
            echo json_encode(['success' => false, 'error' => 'Push not configured']);
            return;
        }

        echo json_encode(['success' => true]);
    }

    /**
     * Send a push notification to a user by uid.
     */
    public static function sendToUser(int $uid, string $title, string $body): void
    {
        try {
            $pdo  = getDB();
            $stmt = $pdo->prepare("SELECT * FROM push_subscriptions WHERE uid = ?");
            $stmt->execute([$uid]);
            $subs = $stmt->fetchAll();
        } catch (PDOException $e) {
            // Table not yet created — skip silently
            return;
        }

        foreach ($subs as $sub) {
            self::sendPush($sub['endpoint'], $sub['p256dh'], $sub['auth_key'], $title, $body);
        }
    }

    private static function sendPush(
        string $endpoint,
        string $p256dh,
        string $authKey,
        string $title,
        string $body,
    ): void {
        $payload = json_encode(['title' => $title, 'body' => $body]);

        $vapidPublic  = $_ENV['VAPID_PUBLIC_KEY']  ?? '';
        $vapidPrivate = $_ENV['VAPID_PRIVATE_KEY'] ?? '';
        $subject      = $_ENV['VAPID_SUBJECT']     ?? '';

        if (!$vapidPublic || !$vapidPrivate) {
            return;
        }

        $parsed   = parse_url($endpoint);
        $audience = $parsed['scheme'] . '://' . $parsed['host'];

        $header   = self::base64url(json_encode(['typ' => 'JWT', 'alg' => 'ES256']));
        $claims   = self::base64url(json_encode([
            'aud' => $audience,
            'exp' => time() + 43200,
            'sub' => $subject,
        ]));
        $sigInput = "$header.$claims";

        $privateKeyPem = self::vapidToPem($vapidPrivate);
        $pkey = openssl_pkey_get_private($privateKeyPem);
        if (!$pkey) {
            return;
        }
        openssl_sign($sigInput, $sig, $pkey, OPENSSL_ALGO_SHA256);
        $jwt = "$sigInput." . self::base64url($sig);

        $headers = [
            'Authorization: vapid t=' . $jwt . ',k=' . $vapidPublic,
            'Content-Type: application/octet-stream',
            'Content-Encoding: aes128gcm',
            'TTL: 86400',
        ];

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_RETURNTRANSFER => true,
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    private static function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function vapidToPem(string $key): string
    {
        $decoded = base64_decode(strtr($key, '-_', '+/') . str_repeat('=', (4 - strlen($key) % 4) % 4));
        return "-----BEGIN EC PRIVATE KEY-----\n"
               . chunk_split(base64_encode(
                   "\x30\x41\x02\x01\x00\x30\x13\x06\x07\x2a\x86\x48\xce\x3d\x02\x01"
                   . "\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07\x04\x27\x30\x25\x02\x01"
                   . "\x01\x04\x20" . $decoded,
               ), 64, "\n")
               . "-----END EC PRIVATE KEY-----";
    }
}
