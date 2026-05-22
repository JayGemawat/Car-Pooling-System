<?php

class Comment {

    public static function getByCarpoolId(int $cid): array {
        $pdo  = getDB();
        $stmt = $pdo->prepare('SELECT * FROM comments WHERE cid = ? ORDER BY slno ASC');
        $stmt->execute([$cid]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): bool {
        $pdo  = getDB();
        $stmt = $pdo->prepare('INSERT INTO comments (sender, comment, cid) VALUES (?, ?, ?)');
        return $stmt->execute([
            $data['sender'],
            $data['comment'],
            $data['cid'],
        ]);
    }
}
