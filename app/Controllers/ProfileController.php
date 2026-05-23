<?php

class ProfileController
{
    public function show(): void
    {
        AuthMiddleware::require();

        $currentUserId = AuthMiddleware::userId();

        // Support viewing another user's profile via ?id=
        $viewId = isset($_GET['id']) ? (int) $_GET['id'] : $currentUserId;
        $isOwnProfile = ($viewId === $currentUserId);

        $user     = User::findById($viewId);
        if (!$user) {
            http_response_code(404);
            echo '<div class="container mt-4"><div class="alert alert-danger">User not found.</div></div>';
            return;
        }

        $rides    = Offer::getByUserId($viewId);
        $allUsers = User::allByCredits();
        $badge    = self::computeBadge($viewId, $allUsers);

        require __DIR__ . '/../Views/profile/index.php';
    }

    public function update(): void
    {
        AuthMiddleware::require();
        AuthMiddleware::verifyCsrf();

        $uid         = AuthMiddleware::userId();
        $name        = trim($_POST['name']        ?? '');
        $gender      = trim($_POST['sex']         ?? 'M');
        $contactno   = trim($_POST['contactno']   ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($name === '' || $contactno === '') {
            redirect('/profile?nerror=1');
        }

        $sex = ($gender === 'female') ? 'F' : 'M';

        User::update($uid, [
            'name'        => $name,
            'gender'      => $sex,
            'contactno'   => (int) $contactno,
            'description' => $description,
        ]);

        redirect('/profile?changed=1');
    }

    public static function computeBadge(int $uid, array $allUsers): string
    {
        $total  = count($allUsers);
        $top    = (int) ceil($total / 3);
        $middle = (int) ceil($total * 2 / 3);
        $rank   = 1;

        foreach ($allUsers as $u) {
            if ((int) $u['uid'] === $uid) {
                break;
            }
            $rank++;
        }

        if ($rank <= $top) {
            return 'Trusted Car Pooler';
        }
        if ($rank <= $middle) {
            return 'Budding Car Pooler';
        }
        return 'Newbie in town';
    }
}
