<?php

namespace App\Repository;

use PDO;

final class CityRepository
{
    /** City columns with country and visit statistics. */
    private const SELECT = 'SELECT ci.id, ci.name, ci.is_capital, co.id AS country_id, co.name AS country,
            co.code AS country_code, COUNT(v.id) AS visit_count, AVG(v.rating) AS average_rating
        FROM cities ci
        JOIN countries co ON co.id = ci.country_id
        LEFT JOIN visits v ON v.city_id = ci.id';

    private const GROUP = ' GROUP BY ci.id, ci.name, ci.is_capital, co.id, co.name, co.code';

    public function __construct(private readonly PDO $pdo)
    {
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(self::SELECT . ' WHERE ci.id = ?' . self::GROUP);
        $stmt->execute([$id]);

        return $stmt->fetch() ?: null;
    }

    public function exists(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM cities WHERE id = ?');
        $stmt->execute([$id]);

        return (bool) $stmt->fetchColumn();
    }

    /** Cities matching a search text and/or a country, most visited first. */
    public function search(string $query, ?int $countryId, int $limit, int $offset): array
    {
        [$where, $params] = $this->filters($query, $countryId);
        $stmt = $this->pdo->prepare(
            self::SELECT . $where . self::GROUP . ' ORDER BY visit_count DESC, ci.name LIMIT ? OFFSET ?'
        );
        $stmt->execute([...$params, $limit, $offset]);

        return $stmt->fetchAll();
    }

    public function countSearch(string $query, ?int $countryId): int
    {
        [$where, $params] = $this->filters($query, $countryId);
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM cities ci JOIN countries co ON co.id = ci.country_id' . $where
        );
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function mostVisited(int $limit): array
    {
        $stmt = $this->pdo->prepare(
            self::SELECT . self::GROUP . ' HAVING COUNT(v.id) > 0 ORDER BY visit_count DESC, average_rating DESC, ci.name LIMIT ?'
        );
        $stmt->execute([$limit]);

        return $stmt->fetchAll();
    }

    /** @return array<string, list<array{id: int, name: string}>> cities grouped by country, for a select */
    public function groupedByCountry(): array
    {
        $rows = $this->pdo->query(
            'SELECT ci.id, ci.name, co.name AS country
             FROM cities ci JOIN countries co ON co.id = ci.country_id
             ORDER BY co.name, ci.name'
        )->fetchAll();

        $groups = [];
        foreach ($rows as $row) {
            $groups[$row['country']][] = ['id' => (int) $row['id'], 'name' => $row['name']];
        }

        return $groups;
    }

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM cities')->fetchColumn();
    }

    private function filters(string $query, ?int $countryId): array
    {
        $conditions = [];
        $params = [];
        if ($query !== '') {
            // "!" escapes the LIKE wildcards typed by the visitor, the same way in MySQL and SQLite.
            $conditions[] = "(ci.name LIKE ? ESCAPE '!' OR co.name LIKE ? ESCAPE '!')";
            $like = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $query) . '%';
            array_push($params, $like, $like);
        }
        if ($countryId !== null) {
            $conditions[] = 'co.id = ?';
            $params[] = $countryId;
        }

        return [$conditions ? ' WHERE ' . implode(' AND ', $conditions) : '', $params];
    }
}
