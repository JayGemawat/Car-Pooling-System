<?php

class RateLimitMiddleware
{
    /**
     * Limit requests per IP per window.
     * The rate_limits table must already exist (created by schema.sql).
     */
    public static function check(string $action, int $maxHits = 10, int $windowSeconds = 60): void
    {
        $ip  = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $key = $action . ':' . $ip;
        $now = time();

        try {
            $pdo = getDB();

            // Clean expired entries (cheap — indexed on window_end)
            $pdo->prepare('DELETE FROM rate_limits WHERE window_end < ?')->execute([$now]);

            $stmt = $pdo->prepare('SELECT id, hits FROM rate_limits WHERE key = ?');
            $stmt->execute([$key]);
            $row = $stmt->fetch();

            if (!$row) {
                $pdo->prepare('INSERT INTO rate_limits (key, hits, window_end) VALUES (?, 1, ?)')
                    ->execute([$key, $now + $windowSeconds]);
                return;
            }

            if ((int)$row['hits'] >= $maxHits) {
                http_response_code(429);
                header('Content-Type: application/json');
                die(json_encode(['error' => 'Too many requests. Please wait a moment.']));
            }

            $pdo->prepare('UPDATE rate_limits SET hits = hits + 1 WHERE id = ?')
                ->execute([$row['id']]);
        } catch (PDOException $e) {
            // Rate limit table missing — fail open (don't block the user)
            error_log('RateLimit error: ' . $e->getMessage());
        }
    }
}
