<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/db.php';

final class Auth
{
    public const HASH_OPTIONS = [
        'memory_cost' => 65536,
        'time_cost'   => 4,
        'threads'     => 1,
    ];

    /** User row without password_hash, or null */
    public static function findByEmail(string $email): ?array
    {
        $stmt = db()->prepare(
            'SELECT id, name, email, password_hash, role
             FROM users
             WHERE email = :email
             LIMIT 1'
        );
        $stmt->execute([':email' => $email]);

        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }

        return self::mapUserRow($row);
    }

    /** User row without password_hash, or null */
    public static function findById(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }

        $stmt = db()->prepare(
            'SELECT id, name, email, password_hash, role
             FROM users
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }

        return self::mapUserRow($row);
    }

    public static function createCustomer(string $name, string $email, string $password): int
    {
        $hash = password_hash($password, PASSWORD_ARGON2ID, self::HASH_OPTIONS);
        if ($hash === false) {
            throw new RuntimeException('Failed to hash password.');
        }

        try {
            $stmt = db()->prepare(
                'INSERT INTO users (name, email, password_hash, role)
                 VALUES (:name, :email, :password_hash, :role)'
            );
            $stmt->execute([
                ':name'          => $name,
                ':email'         => $email,
                ':password_hash' => $hash,
                ':role'          => 'customer',
            ]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000' || (int) ($e->errorInfo[1] ?? 0) === 1062) {
                throw new RuntimeException('That email is already registered.');
            }

            throw $e;
        }

        return (int) db()->lastInsertId();
    }

    public static function attempt(string $email, string $password): ?array
    {
        $stmt = db()->prepare(
            'SELECT id, name, email, password_hash, role
             FROM users
             WHERE email = :email
             LIMIT 1'
        );
        $stmt->execute([':email' => $email]);

        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }

        if (!password_verify($password, (string) $row['password_hash'])) {
            return null;
        }

        return self::mapUserRow($row);
    }

    public static function establishSession(array $user): void
    {
        session_regenerate_id(true);

        $_SESSION['user_id']       = (int) $user['id'];
        $_SESSION['user_email']    = (string) $user['email'];
        $_SESSION['user_name']     = (string) $user['name'];
        $_SESSION['user_role']     = (string) $user['role'];
        $_SESSION['last_activity'] = time();

        $stmt = db()->prepare(
            'UPDATE users
             SET last_login_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([':id' => (int) $user['id']]);
    }

    public static function id(): ?int
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        return (int) $_SESSION['user_id'];
    }

    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function isAdmin(): bool
    {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    public static function requireLogin(): void
    {
        if (!self::isLoggedIn()) {
            header('Location: login.php');
            exit;
        }
    }

    public static function requireAdmin(): void
    {
        if (!self::isLoggedIn()) {
            header('Location: login.php');
            exit;
        }

        $userId = self::id();
        $user = $userId !== null ? self::findById($userId) : null;
        if ($user === null || ($user['role'] ?? '') !== 'admin') {
            header('Location: index.php');
            exit;
        }

        $_SESSION['user_role'] = (string) $user['role'];
    }

    /** @param array<string,mixed> $row */
    private static function mapUserRow(array $row): array
    {
        return [
            'id'    => (int) $row['id'],
            'name'  => (string) $row['name'],
            'email' => (string) $row['email'],
            'role'  => (string) $row['role'],
        ];
    }
}
