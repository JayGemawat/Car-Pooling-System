<?php

declare(strict_types=1);

// All timestamps stored and compared in UTC
date_default_timezone_set('UTC');

require_once __DIR__ . '/../config/env.php';
loadEnv(__DIR__ . '/../.env');

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../app/Middleware/RateLimitMiddleware.php';
require_once __DIR__ . '/../app/Models/User.php';
require_once __DIR__ . '/../app/Models/Offer.php';
require_once __DIR__ . '/../app/Models/Notification.php';
require_once __DIR__ . '/../app/Models/Comment.php';
require_once __DIR__ . '/../app/Controllers/AuthController.php';
require_once __DIR__ . '/../app/Controllers/RideController.php';
require_once __DIR__ . '/../app/Controllers/ProfileController.php';
require_once __DIR__ . '/../app/Controllers/NotificationController.php';
require_once __DIR__ . '/../app/Controllers/PaymentController.php';
require_once __DIR__ . '/../app/Controllers/PushController.php';

// Start session before routing
$sessionName = $_ENV['SESSION_NAME'] ?? 'jaanahai_session';
session_name($sessionName);

// Secure session cookie settings
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
           || (int)($_SERVER['SERVER_PORT'] ?? 80) === 443;
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// Security headers on every response
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

// Rate limiting on sensitive POST endpoints
$requestPath = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';
if (
    in_array($requestPath, ['/login', '/register', '/ride/request', '/payment/create'], true)
    && $_SERVER['REQUEST_METHOD'] === 'POST'
) {
    RateLimitMiddleware::check($requestPath, 10, 60);
}

require_once __DIR__ . '/../routes/web.php';
