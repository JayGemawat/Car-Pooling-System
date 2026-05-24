<?php

return [
    'env'     => $_ENV['APP_ENV']  ?? 'production',
    'url'     => $_ENV['APP_URL']  ?? 'http://localhost',
    'session' => $_ENV['SESSION_NAME'] ?? 'jaanahai_session',
];

/**
 * Redirect to a path, respecting APP_URL so the port is preserved.
 * Usage: redirect('/login?error=1');
 */
function redirect(string $path): never
{
    $base = rtrim($_ENV['APP_URL'] ?? '', '/');
    header('Location: ' . $base . $path);
    exit;
}

/**
 * Format a UTC timestamp string for display in the app's local timezone.
 * Usage: formatTime($ride['uptime'])  →  "Sat, 25 May 2026  14:30"
 */
function formatTime(string $utcTimestamp, string $format = 'D, d M Y  H:i'): string
{
    try {
        $dt = new DateTime($utcTimestamp, new DateTimeZone('UTC'));
        $dt->setTimezone(new DateTimeZone($_ENV['APP_TIMEZONE'] ?? 'Asia/Kolkata'));
        return $dt->format($format);
    } catch (Exception $e) {
        return $utcTimestamp; // fallback: return raw value
    }
}
