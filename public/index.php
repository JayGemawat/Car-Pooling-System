<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/env.php';
loadEnv(__DIR__ . '/../.env');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../app/Models/User.php';
require_once __DIR__ . '/../app/Models/Offer.php';
require_once __DIR__ . '/../app/Models/Notification.php';
require_once __DIR__ . '/../app/Models/Comment.php';
require_once __DIR__ . '/../app/Controllers/AuthController.php';
require_once __DIR__ . '/../app/Controllers/RideController.php';
require_once __DIR__ . '/../app/Controllers/ProfileController.php';
require_once __DIR__ . '/../app/Controllers/NotificationController.php';

// Start session before routing
$sessionName = $_ENV['SESSION_NAME'] ?? 'jaanahai_session';
session_name($sessionName);
session_start();

require_once __DIR__ . '/../routes/web.php';
