<?php

class AuthMiddleware {

    public static function require(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
    }

    public static function guest(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!empty($_SESSION['user_id'])) {
            header('Location: /');
            exit;
        }
    }

    public static function userId(): int {
        return (int) ($_SESSION['user_id'] ?? 0);
    }

    /** Generate a CSRF token and store it in the session. */
    public static function csrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /** Verify the CSRF token from a POST request. Exits on failure. */
    public static function verifyCsrf(): void {
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            die('Invalid request — CSRF token mismatch.');
        }
    }
}
