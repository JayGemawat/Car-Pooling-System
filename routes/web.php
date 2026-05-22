<?php

/**
 * Simple router. Matches exact paths and calls the handler.
 * Dynamic segments (e.g. /ride/123) are matched with regex before this file is loaded.
 */
function route(string $method, string $path, callable $handler): void {
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    $requestPath   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $requestPath   = rtrim($requestPath, '/') ?: '/';

    if (strtoupper($requestMethod) === strtoupper($method) && $requestPath === $path) {
        $handler();
        exit;
    }
}

$requestPath = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';

// ── Dynamic route: /ride/{id} ──────────────────────────────────────────────
if (preg_match('#^/ride/(\d+)$#', $requestPath, $m)) {
    (new RideController())->show((int) $m[1]);
    exit;
}

// ── Dynamic route: /profile?id={id} (view another user's profile) ─────────
// Handled inside ProfileController::show() via $_GET['id']

// ── Static routes ──────────────────────────────────────────────────────────
route('GET',  '/',              fn() => (new RideController())->index());
route('GET',  '/login',         fn() => (new AuthController())->showLogin());
route('POST', '/login',         fn() => (new AuthController())->login());
route('GET',  '/register',      fn() => (new AuthController())->showRegister());
route('POST', '/register',      fn() => (new AuthController())->register());
route('GET',  '/logout',        fn() => (new AuthController())->logout());
route('GET',  '/search',        fn() => (new RideController())->index());   // show form
route('POST', '/search',        fn() => (new RideController())->search());
route('GET',  '/share',         fn() => (new RideController())->showShare());
route('POST', '/share',         fn() => (new RideController())->share());
route('GET',  '/profile',       fn() => (new ProfileController())->show());
route('POST', '/profile',       fn() => (new ProfileController())->update());
route('GET',  '/notifications', fn() => (new NotificationController())->index());
route('POST', '/notifications', fn() => (new NotificationController())->update());
route('POST', '/ride/request',  fn() => (new NotificationController())->requestRide());
route('POST', '/payment/create', fn() => (new PaymentController())->createOrder());
route('POST', '/payment/verify', fn() => (new PaymentController())->verifyPayment());
route('POST', '/push/subscribe', fn() => (new PushController())->subscribe());

// ── 404 fallback ───────────────────────────────────────────────────────────
http_response_code(404);
echo '404 — Page not found';
exit;
