<?php

namespace App\Repository;

use PDO;

final class CountryRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** Countries that have at least one city, for the filter menu. */
    public function all(): array
    {
        return $this->pdo->query(
            'SELECT co.id, co.name, co.code, COUNT(ci.id) AS city_count
             FROM countries co
             JOIN cities ci ON ci.country_id = co.id
             GROUP BY co.id, co.name, co.code
             ORDER BY co.name'
        )->fetchAll();
    }
}
