<?php

namespace App\Repository;

use PDO;

final class UserRepository
{
    /** Never selects the password hash: it is only read by findForLogin(). */
    private const SELECT = 'SELECT u.id, u.pseudo, u.first_name, u.last_name, u.birth_date, u.created_at,
            u.city_id, ci.name AS city, co.name AS country, co.nationality
        FROM users u
        JOIN cities ci ON ci.id = u.city_id
        JOIN countries co ON co.id = ci.country_id';

    public function __construct(private readonly PDO $pdo)
    {
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(self::SELECT . ' WHERE u.id = ?');
        $stmt->execute([$id]);

        return $stmt->fetch() ?: null;
    }

    public function findByPseudo(string $pseudo): ?array
    {
        $stmt = $this->pdo->prepare(self::SELECT . ' WHERE u.pseudo = ?');
        $stmt->execute([$pseudo]);

        return $stmt->fetch() ?: null;
    }

    /** @return array{id: int, password_hash: string}|null */
    public function findForLogin(string $pseudo): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, password_hash FROM users WHERE pseudo = ?');
        $stmt->execute([$pseudo]);

        return $stmt->fetch() ?: null;
    }

    public function pseudoExists(string $pseudo): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM users WHERE LOWER(pseudo) = LOWER(?)');
        $stmt->execute([$pseudo]);

        return (bool) $stmt->fetchColumn();
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (pseudo, first_name, last_name, password_hash, birth_date, city_id, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['pseudo'],
            $data['first_name'],
            $data['last_name'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['birth_date'],
            $data['city_id'],
            date('Y-m-d H:i:s'),
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function updateProfile(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users SET first_name = ?, last_name = ?, birth_date = ?, city_id = ? WHERE id = ?'
        );
        $stmt->execute([$data['first_name'], $data['last_name'], $data['birth_date'], $data['city_id'], $id]);
    }

    /** Re-hashes the password when PHP's default algorithm has changed since it was saved. */
    public function rehashIfNeeded(int $id, string $password, string $hash): void
    {
        if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
            $stmt = $this->pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
            $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
        }
    }

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }
}
