<?php
declare(strict_types=1);

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
session_start();

// Rate limiting on sensitive POST endpoints
$requestPath = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';
if (in_array($requestPath, ['/login', '/register', '/ride/request', '/payment/create'], true)
    && $_SERVER['REQUEST_METHOD'] === 'POST') {
    RateLimitMiddleware::check($requestPath, 10, 60);
}

require_once __DIR__ . '/../routes/web.php';
