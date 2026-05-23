<?php

class User
{
    public static function findByEmail(string $email): ?array
    {
        $pdo  = getDB();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findById(int $uid): ?array
    {
        $pdo  = getDB();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE uid = ?');
        $stmt->execute([$uid]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): bool
    {
        $pdo  = getDB();
        $hash = password_hash($data['password'], PASSWORD_BCRYPT);
        $stmt = $pdo->prepare(
            'INSERT INTO users (name, hash, email, gender, contactno, description)
             VALUES (?, ?, ?, ?, ?, ?)',
        );
        return $stmt->execute([
            $data['name'],
            $hash,
            $data['email'],
            $data['gender'],
            $data['contactno'],
            $data['description'] ?? null,
        ]);
    }

    public static function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    public static function update(int $uid, array $data): bool
    {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            'UPDATE users SET name = ?, gender = ?, contactno = ?, description = ? WHERE uid = ?',
        );
        return $stmt->execute([
            $data['name'],
            $data['gender'],
            $data['contactno'],
            $data['description'] ?? null,
            $uid,
        ]);
    }

    /** Returns all users ordered by credits DESC (for badge ranking). */
    public static function allByCredits(): array
    {
        $pdo  = getDB();
        $stmt = $pdo->query('SELECT uid, credits FROM users ORDER BY credits DESC');
        return $stmt->fetchAll();
    }
}
