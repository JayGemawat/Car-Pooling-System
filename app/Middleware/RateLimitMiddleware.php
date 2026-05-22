<?php

class RateLimitMiddleware {

    /**
     * Limit requests per IP per window.
     */
    public static function check(string $action, int $maxHits = 10, int $windowSeconds = 60): void {
        $ip  = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $key = $action . ':' . $ip;
        $now = time();

        $pdo = getDB();

        // Ensure table exists
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS rate_limits (
                id         SERIAL PRIMARY KEY,
                key        VARCHAR(255) NOT NULL,
                hits       INTEGER NOT NULL DEFAULT 1,
                window_end BIGINT  NOT NULL
            )
        ");

        // Clean expired entries
        $pdo->prepare("DELETE FROM rate_limits WHERE window_end < ?")->execute([$now]);

        $stmt = $pdo->prepare("SELECT id, hits FROM rate_limits WHERE key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch();

        if (!$row) {
            $pdo->prepare("INSERT INTO rate_limits (key, hits, window_end) VALUES (?, 1, ?)")
                ->execute([$key, $now + $windowSeconds]);
            return;
        }

        if ($row['hits'] >= $maxHits) {
            http_response_code(429);
            header('Content-Type: application/json');
            die(json_encode(['error' => 'Too many requests. Please wait a moment.']));
        }

        $pdo->prepare("UPDATE rate_limits SET hits = hits + 1 WHERE id = ?")
            ->execute([$row['id']]);
    }
}
