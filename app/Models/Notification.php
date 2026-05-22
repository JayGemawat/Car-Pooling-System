<?php

class Notification {

    public static function getForUser(int $uid): array {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            'SELECT * FROM notifications WHERE receiver = ? ORDER BY timestamp DESC'
        );
        $stmt->execute([$uid]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): bool {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            'INSERT INTO notifications (sender, receiver, type, cid, status)
             VALUES (?, ?, ?, ?, ?)'
        );
        return $stmt->execute([
            $data['sender'],
            $data['receiver'],
            $data['type'],
            $data['cid'],
            $data['status'] ?? null,
        ]);
    }

    public static function markRead(int $slno): bool {
        $pdo  = getDB();
        $stmt = $pdo->prepare("UPDATE notifications SET status = 'read' WHERE slno = ?");
        return $stmt->execute([$slno]);
    }

    public static function findById(int $slno): ?array {
        $pdo  = getDB();
        $stmt = $pdo->prepare('SELECT * FROM notifications WHERE slno = ?');
        $stmt->execute([$slno]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function updateStatus(int $slno, string $status): bool {
        $pdo  = getDB();
        $stmt = $pdo->prepare('UPDATE notifications SET status = ? WHERE slno = ?');
        return $stmt->execute([$status, $slno]);
    }
}
