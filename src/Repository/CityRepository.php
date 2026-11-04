<?php

namespace App\Repository;

use PDO;

final class CityRepository
{
    /** City columns with country and visit statistics. */
    private const SELECT = 'SELECT ci.id, ci.name, ci.region, ci.is_capital, co.id AS country_id, co.name AS country,
            co.code AS country_code, COUNT(v.id) AS visit_count, AVG(v.rating) AS average_rating
        FROM cities ci
        JOIN countries co ON co.id = ci.country_id
        LEFT JOIN visits v ON v.city_id = ci.id';

    private const GROUP = ' GROUP BY ci.id, ci.name, ci.region, ci.is_capital, co.id, co.name, co.code';

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

    /** Id of the city already saved for a place of the world search, or null. */
    public function idForPlace(array $place): ?int
    {
        $stmt = $this->pdo->prepare('SELECT id FROM cities WHERE place_ref = ?');
        $stmt->execute([$place['ref']]);
        $id = $stmt->fetchColumn();
        if ($id === false && $place['kind'] === 'city') {
            // The demo cities have no reference: a big city with the same name in the same country is the same one.
            $stmt = $this->pdo->prepare(
                'SELECT ci.id FROM cities ci JOIN countries co ON co.id = ci.country_id
                 WHERE ci.place_ref IS NULL AND co.code = ? AND ci.name = ?'
            );
            $stmt->execute([$place['country_code'], $place['name']]);
            $id = $stmt->fetchColumn();
        }

        return $id === false ? null : (int) $id;
    }

    /** Saves a city found by the world search, with its country if it is new, and returns its id. */
    public function createFromPlace(array $place): int
    {
        $id = $this->idForPlace($place);
        if ($id !== null) {
            $this->pdo->prepare('UPDATE cities SET place_ref = ? WHERE id = ? AND place_ref IS NULL')->execute([$place['ref'], $id]);

            return $id;
        }

        $this->pdo->prepare('INSERT INTO cities (country_id, name, region, place_ref) VALUES (?, ?, ?, ?)')
            ->execute([$this->countryId($place['country_code'], $place['country']), $place['name'], $place['region'], $place['ref']]);

        return (int) $this->pdo->lastInsertId();
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

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM cities')->fetchColumn();
    }

    private function countryId(string $code, string $name): int
    {
        $stmt = $this->pdo->prepare('SELECT id FROM countries WHERE code = ?');
        $stmt->execute([$code]);
        $id = $stmt->fetchColumn();
        if ($id !== false) {
            return (int) $id;
        }
        // The nationality is only known for the demo countries.
        $this->pdo->prepare("INSERT INTO countries (name, code, nationality) VALUES (?, ?, '')")->execute([$name, $code]);

        return (int) $this->pdo->lastInsertId();
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
