<?php

namespace Tests;

use App\Places\PlaceFinder;
use App\Places\PlaceSearchUnavailable;

/** Answers city searches from a fixed list, so the tests never call the real service. */
final class FakePlaceFinder implements PlaceFinder
{
    public bool $down = false;

    private const PLACES = [
        ['ref' => 'R2689', 'name' => 'Kyoto', 'region' => 'Préfecture de Kyoto', 'country' => 'Japon', 'country_code' => 'JP', 'kind' => 'city'],
        ['ref' => 'R2690', 'name' => 'Kyotango', 'region' => 'Préfecture de Kyoto', 'country' => 'Japon', 'country_code' => 'JP', 'kind' => 'town'],
        ['ref' => 'R71525', 'name' => 'Paris', 'region' => 'Île-de-France', 'country' => 'France', 'country_code' => 'FR', 'kind' => 'city'],
        ['ref' => 'N4001', 'name' => 'Paris', 'region' => 'Texas', 'country' => 'États-Unis', 'country_code' => 'US', 'kind' => 'town'],
    ];

    public function search(string $query): array
    {
        if ($this->down) {
            throw new PlaceSearchUnavailable('down');
        }

        return array_values(array_filter(
            self::PLACES,
            fn (array $place) => $query !== '' && stripos($place['name'], $query) === 0,
        ));
    }
}
