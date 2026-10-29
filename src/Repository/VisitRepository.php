<?php

namespace App\Repository;

use PDO;

final class VisitRepository
{
    private const SELECT = 'SELECT v.id, v.user_id, v.city_id, v.visited_on, v.rating, v.note,
            ci.name AS city, co.name AS country, co.code AS country_code, u.pseudo
        FROM visits v
        JOIN cities ci ON ci.id = v.city_id
        JOIN countries co ON co.id = ci.country_id
        JOIN users u ON u.id = v.user_id';

    public function __construct(private readonly PDO $pdo)
    {
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(self::SELECT . ' WHERE v.id = ?');
        $stmt->execute([$id]);

        return $stmt->fetch() ?: null;
    }

    /** A member's journal, most recent trip first. */
    public function forUser(int $userId): array
    {
        $stmt = $this->pdo->prepare(self::SELECT . ' WHERE v.user_id = ? ORDER BY v.visited_on DESC, v.id DESC');
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }

    public function forCity(int $cityId, int $limit): array
    {
        $stmt = $this->pdo->prepare(self::SELECT . ' WHERE v.city_id = ? ORDER BY v.visited_on DESC, v.id DESC LIMIT ?');
        $stmt->execute([$cityId, $limit]);

        return $stmt->fetchAll();
    }

    public function latest(int $limit): array
    {
        $stmt = $this->pdo->prepare(self::SELECT . ' ORDER BY v.created_at DESC, v.id DESC LIMIT ?');
        $stmt->execute([$limit]);

        return $stmt->fetchAll();
    }

    /** Has this member already got this city in their journal (ignoring one entry being edited)? */
    public function hasCity(int $userId, int $cityId, ?int $exceptId = null): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM visits WHERE user_id = ? AND city_id = ? AND id <> ?');
        $stmt->execute([$userId, $cityId, $exceptId ?? 0]);

        return (bool) $stmt->fetchColumn();
    }

    public function create(int $userId, array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO visits (user_id, city_id, visited_on, rating, note, created_at) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $data['city_id'], $data['visited_on'], $data['rating'], $data['note'], date('Y-m-d H:i:s')]);

        return (int) $this->pdo->lastInsertId();
    }

    /** Only updates the entry if it belongs to this member. */
    public function update(int $id, int $userId, array $data): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE visits SET city_id = ?, visited_on = ?, rating = ?, note = ? WHERE id = ? AND user_id = ?'
        );
        $stmt->execute([$data['city_id'], $data['visited_on'], $data['rating'], $data['note'], $id, $userId]);
    }

    public function delete(int $id, int $userId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM visits WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
    }

    /** @return array{cities: int, countries: int, average: float|null} */
    public function statsForUser(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) AS cities, COUNT(DISTINCT ci.country_id) AS countries, AVG(v.rating) AS average
             FROM visits v JOIN cities ci ON ci.id = v.city_id
             WHERE v.user_id = ?'
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        return [
            'cities' => (int) $row['cities'],
            'countries' => (int) $row['countries'],
            'average' => $row['average'] === null ? null : (float) $row['average'],
        ];
    }

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM visits')->fetchColumn();
    }
}
