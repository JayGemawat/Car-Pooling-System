<?php

class AuthController {

    public function showLogin(): void {
        AuthMiddleware::guest();
        require __DIR__ . '/../Views/auth/login.php';
    }

    public function login(): void {
        AuthMiddleware::guest();
        AuthMiddleware::verifyCsrf();

        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            header('Location: /login?nerror=1');
            exit;
        }

        $user = User::findByEmail($email);

        if (!$user || !User::verifyPassword($password, $user['hash'])) {
            header('Location: /login?error=1');
            exit;
        }

        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['uid'];
        $_SESSION['user_name'] = $user['name'];

        header('Location: /');
        exit;
    }

    public function showRegister(): void {
        AuthMiddleware::guest();
        require __DIR__ . '/../Views/auth/register.php';
    }

    public function register(): void {
        AuthMiddleware::guest();
        AuthMiddleware::verifyCsrf();

        $name        = trim($_POST['name']        ?? '');
        $email       = trim($_POST['email']       ?? '');
        $password    = trim($_POST['password']    ?? '');
        $contactno   = trim($_POST['contactno']   ?? '');
        $gender      = trim($_POST['sex']         ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($name === '' || $email === '' || $password === '' || $contactno === '') {
            header('Location: /register?nerror=1');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: /register?nerror=1');
            exit;
        }

        if (User::findByEmail($email)) {
            header('Location: /register?exists=1');
            exit;
        }

        $sex = ($gender === 'female') ? 'F' : 'M';

        User::create([
            'name'        => $name,
            'email'       => $email,
            'password'    => $password,
            'gender'      => $sex,
            'contactno'   => (int) $contactno,
            'description' => $description,
        ]);

        require_once __DIR__ . '/../Mail/Mailer.php';
        Mailer::sendWelcome($email, $name);

        header('Location: /login?registered=1');
        exit;
    }

    public function logout(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION = [];
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 42000, '/');
        }
        session_destroy();
        header('Location: /login?logout=1');
        exit;
    }
}
