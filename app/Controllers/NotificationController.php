<?php

class NotificationController {

    public function index(): void {
        AuthMiddleware::require();

        $uid           = AuthMiddleware::userId();
        $notifications = Notification::getForUser($uid);

        $offerCache = [];
        foreach ($notifications as $n) {
            $cid = (int) $n['cid'];
            if (!isset($offerCache[$cid])) {
                $offerCache[$cid] = Offer::findById($cid);
            }
        }

        require __DIR__ . '/../Views/notifications/index.php';
    }

    public function update(): void {
        AuthMiddleware::require();
        AuthMiddleware::verifyCsrf();

        $type = (int) ($_POST['type']     ?? 0);
        $slno = (int) ($_POST['serialNo'] ?? 0);

        if ($type === 1) {
            $stat = trim($_POST['stat'] ?? '');
            if (!in_array($stat, ['Approved', 'Declined'], true)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid status']);
                return;
            }

            $notif = Notification::findById($slno);
            if (!$notif) {
                http_response_code(404);
                echo json_encode(['error' => 'Notification not found']);
                return;
            }

            Notification::updateStatus($slno, $stat);

            Notification::create([
                'sender'   => (int) $notif['receiver'],
                'receiver' => (int) $notif['sender'],
                'type'     => 3,
                'cid'      => (int) $notif['cid'],
                'status'   => $stat,
            ]);

            if ($stat === 'Approved') {
                $offer = Offer::findById((int) $notif['cid']);
                if ($offer) {
                    Offer::update((int) $offer['id'], [
                        'from'        => $offer['from'],
                        'to'          => $offer['to'],
                        'uptime'      => $offer['uptime'],
                        'people'      => max(0, (int) $offer['people'] - 1),
                        'price'       => $offer['price'],
                        'vehicle'     => $offer['vehicle'],
                        'description' => $offer['description'],
                    ]);
                }
            }

            echo json_encode(['success' => true]);
            return;
        }

        if ($type === 2) {
            $rating = (int) ($_POST['rating'] ?? 0);
            if ($rating < 1 || $rating > 5) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid rating']);
                return;
            }
            Notification::updateStatus($slno, (string) $rating);
            echo json_encode(['success' => true]);
            return;
        }

        http_response_code(400);
        echo json_encode(['error' => 'Unknown type']);
    }

    public function requestRide(): void {
        AuthMiddleware::require();
        AuthMiddleware::verifyCsrf();

        $uid = AuthMiddleware::userId();
        $cid = (int) ($_POST['cid'] ?? 0);

        $offer = Offer::findById($cid);
        if (!$offer) {
            redirect('/?error=1');
        }

        $ownerId = (int) $offer['uid'];

        Notification::create([
            'sender'   => $uid,
            'receiver' => $uid,
            'type'     => 4,
            'cid'      => $cid,
            'status'   => null,
        ]);

        Notification::create([
            'sender'   => $uid,
            'receiver' => $ownerId,
            'type'     => 1,
            'cid'      => $cid,
            'status'   => null,
        ]);

        // Fire-and-forget emails
        require_once __DIR__ . '/../Mail/Mailer.php';
        $rider  = User::findById($uid);
        $driver = User::findById($ownerId);
        if ($rider && $driver) {
            Mailer::sendRideConfirmation($rider['email'], $rider['name'], $offer);
            Mailer::sendRideRequest($driver['email'], $driver['name'], $rider['name'], $offer);
        }

        redirect('/?success=1');
    }
}
