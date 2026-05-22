<?php

class Offer {

    public static function getUpcoming(): array {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            'SELECT id, "from", "to", uptime, vehicle FROM offers WHERE uptime > NOW() ORDER BY uptime ASC'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array {
        $pdo  = getDB();
        $stmt = $pdo->prepare('SELECT * FROM offers WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function search(string $from, string $to, string $uptime, string $downtime): array {
        $uptime   = $uptime   !== '' ? $uptime   : '1970-01-01 00:00:00';
        $downtime = $downtime !== '' ? $downtime : '2099-12-31 23:59:59';
        $pdo = getDB();

        // Find carpool IDs where the route passes through both from and to in order
        $stmt = $pdo->prepare(
            'SELECT r1.cid FROM route r1
             INNER JOIN route r2 ON r1.cid = r2.cid
             WHERE r1.place = ? AND r2.place = ? AND r1.serialno < r2.serialno'
        );
        $stmt->execute([$from, $to]);
        $cids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($cids)) return [];

        $placeholders = implode(',', array_fill(0, count($cids), '?'));
        $params = array_merge($cids, [$uptime, $downtime]);

        $stmt2 = $pdo->prepare(
            "SELECT id, vehicle, \"from\", \"to\", uptime FROM offers
             WHERE id IN ($placeholders)
             AND uptime >= ? AND uptime <= ?
             ORDER BY uptime ASC"
        );
        $stmt2->execute($params);
        return $stmt2->fetchAll();
    }

    public static function create(array $data): int {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            'INSERT INTO offers (uid, "from", "to", uptime, people, price, vehicle, description)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)
             RETURNING id'
        );
        $stmt->execute([
            $data['uid'],
            $data['from'],
            $data['to'],
            $data['uptime'],
            $data['people']      ?? 1,
            $data['price']       ?? 0,
            $data['vehicle'],
            $data['description'] ?? null,
        ]);
        $row = $stmt->fetch();
        return (int) $row['id'];
    }

    public static function getByUserId(int $uid): array {
        $pdo  = getDB();
        $stmt = $pdo->prepare('SELECT * FROM offers WHERE uid = ? ORDER BY uptime DESC');
        $stmt->execute([$uid]);
        return $stmt->fetchAll();
    }

    public static function update(int $id, array $data): bool {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            'UPDATE offers SET "from" = ?, "to" = ?, uptime = ?, people = ?, price = ?, vehicle = ?, description = ?, status = ?
             WHERE id = ?'
        );
        return $stmt->execute([
            $data['from'],
            $data['to'],
            $data['uptime'],
            $data['people'],
            $data['price'],
            $data['vehicle'],
            $data['description'] ?? null,
            $data['status']      ?? 'open',
            $id,
        ]);
    }

    public static function delete(int $id): bool {
        $pdo  = getDB();
        $stmt = $pdo->prepare('DELETE FROM offers WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function getRouteWaypoints(int $cid): array {
        $pdo  = getDB();
        $stmt = $pdo->prepare('SELECT place FROM route WHERE cid = ? ORDER BY serialno ASC');
        $stmt->execute([$cid]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function addRouteWaypoint(int $cid, string $place, int $serialno): bool {
        $pdo  = getDB();
        $stmt = $pdo->prepare('INSERT INTO route (cid, place, serialno) VALUES (?, ?, ?)');
        return $stmt->execute([$cid, $place, $serialno]);
    }

    /**
     * Calculate distance between two lat/lon pairs in km (Haversine formula).
     */
    public static function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float {
        $R    = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a    = sin($dLat/2) * sin($dLat/2) +
                cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
                sin($dLon/2) * sin($dLon/2);
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * Geocode a place name to [lat, lon] using Nominatim.
     */
    public static function geocodeServer(string $place): ?array {
        $url = 'https://nominatim.openstreetmap.org/search?q=' .
               urlencode($place . ',India') . '&format=json&limit=1';
        $ctx = stream_context_create(['http' => ['header' => "User-Agent: JaanaHai/1.0\r\n"]]);
        $raw = @file_get_contents($url, false, $ctx);
        if (!$raw) return null;
        $data = json_decode($raw, true);
        if (empty($data[0])) return null;
        return [(float)$data[0]['lat'], (float)$data[0]['lon']];
    }

    /**
     * Get distance in km between two place names.
     */
    public static function distanceBetween(string $from, string $to): ?float {
        $f = self::geocodeServer($from);
        $t = self::geocodeServer($to);
        if (!$f || !$t) return null;
        return self::haversineKm($f[0], $f[1], $t[0], $t[1]);
    }
}
