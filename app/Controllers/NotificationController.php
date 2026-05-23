<?php

class NotificationController
{
    public function index(): void
    {
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

    /**
     * GET /notifications/count — returns unseen count as JSON (for bell polling).
     */
    public function count(): void
    {
        AuthMiddleware::require();
        $uid   = AuthMiddleware::userId();
        $count = Notification::countUnseen($uid);
        header('Content-Type: application/json');
        echo json_encode(['count' => $count]);
    }

    /**
     * GET /notifications/recent — returns recent notifications as JSON for the dropdown.
     */
    public function recent(): void
    {
        AuthMiddleware::require();
        $uid   = AuthMiddleware::userId();
        $items = Notification::getRecentForUser($uid, 10);

        // Enrich with offer data
        $offerCache = [];
        foreach ($items as &$n) {
            $cid = (int) $n['cid'];
            if (!isset($offerCache[$cid])) {
                $offerCache[$cid] = Offer::findById($cid);
            }
            $offer = $offerCache[$cid];
            $n['route'] = $offer
                ? $offer['from'] . ' → ' . $offer['to']
                : 'Unknown route';
        }
        unset($n);

        // Mark all as seen now that user opened the panel
        Notification::markAllSeen($uid);

        header('Content-Type: application/json');
        echo json_encode($items);
    }

    /**
     * POST /notifications/delete — soft-delete a notification.
     */
    public function delete(): void
    {
        AuthMiddleware::require();
        AuthMiddleware::verifyCsrf();

        $uid  = AuthMiddleware::userId();
        $slno = (int) ($_POST['slno'] ?? 0);

        if (!$slno) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing slno']);
            return;
        }

        Notification::softDelete($slno, $uid);
        echo json_encode(['success' => true]);
    }

    public function update(): void
    {
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

            // Authorization: only the receiver (driver) can approve/decline
            if ((int)$notif['receiver'] !== AuthMiddleware::userId()) {
                http_response_code(403);
                echo json_encode(['error' => 'Forbidden']);
                return;
            }

            Notification::updateStatus($slno, $stat);

            // Create a status notification for the rider
            Notification::create([
                'sender'   => (int) $notif['receiver'],
                'receiver' => (int) $notif['sender'],
                'type'     => 3,
                'cid'      => (int) $notif['cid'],
                'status'   => $stat,
            ]);

            $offer = Offer::findById((int) $notif['cid']);

            if ($stat === 'Approved') {
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
                // Push notify rider
                PushController::sendToUser((int)$notif['sender'], 'Ride Approved! 🎉', 'Your ride request was approved.');

                // Email the rider
                require_once __DIR__ . '/../Mail/Mailer.php';
                $rider = User::findById((int) $notif['sender']);
                if ($rider && $offer) {
                    Mailer::sendRideApproved($rider['email'], $rider['name'], $offer);
                }
            } else {
                // Declined — email the rider
                require_once __DIR__ . '/../Mail/Mailer.php';
                $rider = User::findById((int) $notif['sender']);
                if ($rider && $offer) {
                    Mailer::sendRideDeclined($rider['email'], $rider['name'], $offer);
                }
                PushController::sendToUser((int)$notif['sender'], 'Ride Request Declined', 'Your ride request was declined.');
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
            $notif = Notification::findById($slno);
            if (!$notif || (int)$notif['receiver'] !== AuthMiddleware::userId()) {
                http_response_code(403);
                echo json_encode(['error' => 'Forbidden']);
                return;
            }
            Notification::updateStatus($slno, (string) $rating);
            echo json_encode(['success' => true]);
            return;
        }

        http_response_code(400);
        echo json_encode(['error' => 'Unknown type']);
    }

    public function requestRide(): void
    {
        AuthMiddleware::require();
        AuthMiddleware::verifyCsrf();

        $uid = AuthMiddleware::userId();
        $cid = (int) ($_POST['cid'] ?? 0);

        $offer = Offer::findById($cid);
        if (!$offer) {
            redirect('/?error=1');
        }

        $ownerId = (int) $offer['uid'];

        // Self-copy notification (type 4 = pending with rider)
        Notification::create([
            'sender'   => $uid,
            'receiver' => $uid,
            'type'     => 4,
            'cid'      => $cid,
            'status'   => null,
        ]);

        // Notification for the driver (type 1 = approve request)
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

        // Push notify driver
        PushController::sendToUser($ownerId, 'New Ride Request', ($rider['name'] ?? 'Someone') . ' wants to join your ride!');

        redirect('/?success=1');
    }
}
