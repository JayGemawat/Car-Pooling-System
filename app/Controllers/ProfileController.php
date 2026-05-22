<?php

class ProfileController {

    public function show(): void {
        AuthMiddleware::require();

        $uid      = AuthMiddleware::userId();
        $user     = User::findById($uid);
        $rides    = Offer::getByUserId($uid);
        $allUsers = User::allByCredits();

        // Compute badge based on credit ranking
        $badge = self::computeBadge($uid, $allUsers);

        require __DIR__ . '/../Views/profile/index.php';
    }

    public function update(): void {
        AuthMiddleware::require();
        AuthMiddleware::verifyCsrf();

        $uid         = AuthMiddleware::userId();
        $name        = trim($_POST['name']        ?? '');
        $gender      = trim($_POST['sex']         ?? 'M');
        $contactno   = trim($_POST['contactno']   ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($name === '' || $contactno === '') {
            header('Location: /profile?nerror=1');
            exit;
        }

        $sex = ($gender === 'female') ? 'F' : 'M';

        User::update($uid, [
            'name'        => $name,
            'gender'      => $sex,
            'contactno'   => (int) $contactno,
            'description' => $description,
        ]);

        header('Location: /profile?changed=1');
        exit;
    }

    public static function computeBadge(int $uid, array $allUsers): string {
        $total  = count($allUsers);
        $top    = (int) ceil($total / 3);
        $middle = (int) ceil($total * 2 / 3);
        $rank   = 1;

        foreach ($allUsers as $u) {
            if ((int) $u['uid'] === $uid) break;
            $rank++;
        }

        if ($rank <= $top)    return 'Trusted Car Pooler';
        if ($rank <= $middle) return 'Budding Car Pooler';
        return 'Newbie in town';
    }
}
