<?php

class RideController {

    public function index(): void {
        AuthMiddleware::require();
        $rides = Offer::getUpcoming();
        require __DIR__ . '/../Views/rides/index.php';
    }

    public function search(): void {
        AuthMiddleware::require();
        AuthMiddleware::verifyCsrf();

        $from     = trim($_POST['from']     ?? '');
        $to       = trim($_POST['to']       ?? '');
        $uptime   = trim($_POST['uptime']   ?? '');
        $downtime = trim($_POST['downtime'] ?? '');

        $results = [];
        if ($from !== '' && $to !== '') {
            $results = Offer::search($from, $to, $uptime, $downtime);
        }

        require __DIR__ . '/../Views/rides/search.php';
    }

    public function show(int $id): void {
        AuthMiddleware::require();
        $ride = Offer::findById($id);
        if (!$ride) {
            http_response_code(404);
            echo "<p>Ride not found.</p>";
            return;
        }
        $waypoints = Offer::getRouteWaypoints($id);
        $rider     = User::findById((int) $ride['uid']);
        require __DIR__ . '/../Views/rides/detail.php';
    }

    public function showShare(): void {
        AuthMiddleware::require();
        require __DIR__ . '/../Views/rides/share.php';
    }

    public function share(): void {
        AuthMiddleware::require();
        AuthMiddleware::verifyCsrf();

        $uid         = AuthMiddleware::userId();
        $from        = trim($_POST['from']           ?? '');
        $to          = trim($_POST['to']             ?? '');
        $uptime      = trim($_POST['uptime']         ?? '');
        $vehicle     = trim($_POST['vehicle']        ?? 'car');
        $number      = (int) ($_POST['number']       ?? 1);
        $cost        = (int) ($_POST['cost']         ?? 0);
        $description = trim($_POST['description']    ?? '');
        $totalVia    = (int) ($_POST['totalRequests'] ?? 0);

        if ($from === '' || $to === '' || $uptime === '') {
            redirect('/share?nerror=1');
        }

        $allowedVehicles = ['car', 'taxi', 'auto'];
        if (!in_array($vehicle, $allowedVehicles, true)) {
            $vehicle = 'car';
        }

        $cid = Offer::create([
            'uid'         => $uid,
            'from'        => $from,
            'to'          => $to,
            'uptime'      => $uptime,
            'people'      => $number,
            'price'       => $cost,
            'vehicle'     => $vehicle,
            'description' => $description,
        ]);

        Offer::addRouteWaypoint($cid, $from, 1);
        for ($i = 1; $i <= $totalVia; $i++) {
            $place = trim($_POST['dynamic' . $i] ?? '');
            if ($place !== '') {
                Offer::addRouteWaypoint($cid, $place, $i + 1);
            }
        }
        Offer::addRouteWaypoint($cid, $to, $totalVia + 2);

        redirect('/?share=1');
    }
}
