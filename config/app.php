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
function redirect(string $path): never {
    $base = rtrim($_ENV['APP_URL'] ?? '', '/');
    header('Location: ' . $base . $path);
    exit;
}
