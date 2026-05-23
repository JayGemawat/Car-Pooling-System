<?php

class Notification
{
    /**
     * Get all non-deleted notifications for a user, newest first.
     */
    public static function getForUser(int $uid): array
    {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            'SELECT * FROM notifications
             WHERE receiver = ? AND deleted_at IS NULL
             ORDER BY timestamp DESC',
        );
        $stmt->execute([$uid]);
        return $stmt->fetchAll();
    }

    /**
     * Count unseen (unread) notifications for a user.
     */
    public static function countUnseen(int $uid): int
    {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM notifications
             WHERE receiver = ? AND seen = FALSE AND deleted_at IS NULL',
        );
        $stmt->execute([$uid]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Mark all notifications as seen for a user (called when they open the panel).
     */
    public static function markAllSeen(int $uid): void
    {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            'UPDATE notifications SET seen = TRUE
             WHERE receiver = ? AND seen = FALSE AND deleted_at IS NULL',
        );
        $stmt->execute([$uid]);
    }

    public static function create(array $data): bool
    {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            'INSERT INTO notifications (sender, receiver, type, cid, status, seen)
             VALUES (?, ?, ?, ?, ?, FALSE)',
        );
        return $stmt->execute([
            $data['sender'],
            $data['receiver'],
            $data['type'],
            $data['cid'],
            $data['status'] ?? null,
        ]);
    }

    public static function markRead(int $slno): bool
    {
        $pdo  = getDB();
        $stmt = $pdo->prepare("UPDATE notifications SET status = 'read', seen = TRUE WHERE slno = ?");
        return $stmt->execute([$slno]);
    }

    public static function findById(int $slno): ?array
    {
        $pdo  = getDB();
        $stmt = $pdo->prepare('SELECT * FROM notifications WHERE slno = ? AND deleted_at IS NULL');
        $stmt->execute([$slno]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function updateStatus(int $slno, string $status): bool
    {
        $pdo  = getDB();
        $stmt = $pdo->prepare('UPDATE notifications SET status = ? WHERE slno = ?');
        return $stmt->execute([$status, $slno]);
    }

    /**
     * Soft-delete a notification (sets deleted_at timestamp).
     */
    public static function softDelete(int $slno, int $uid): bool
    {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            'UPDATE notifications SET deleted_at = NOW()
             WHERE slno = ? AND receiver = ?',
        );
        return $stmt->execute([$slno, $uid]);
    }

    /**
     * Get recent unseen notifications for the bell dropdown (max 10).
     */
    public static function getRecentForUser(int $uid, int $limit = 10): array
    {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            'SELECT * FROM notifications
             WHERE receiver = ? AND deleted_at IS NULL
             ORDER BY timestamp DESC
             LIMIT ?',
        );
        $stmt->execute([$uid, $limit]);
        return $stmt->fetchAll();
    }
}
