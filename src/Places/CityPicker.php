<?php

namespace App\Places;

use App\Repository\CityRepository;

/**
 * Picks a city in Escales or anywhere in the world, for the journal and the profile forms.
 * A choice is written "ville:12" for a saved city and "lieu:R58274" for a city of the world search.
 */
final class CityPicker
{
    public function __construct(
        private readonly CityRepository $cities,
        private readonly PlaceFinder $places,
    ) {
    }

    /**
     * Saved cities first, then the rest of the world.
     * @return array{options: list<array{value: string, name: string, detail: string}>, unavailable: bool}
     */
    public function search(string $query): array
    {
        $options = [];
        if ($query === '') {
            return ['options' => [], 'unavailable' => false];
        }
        foreach ($this->cities->search($query, null, 8, 0) as $city) {
            $options['ville:' . $city['id']] = $this->option('ville:' . $city['id'], $city['name'], $city['region'], $city['country']);
        }

        $unavailable = false;
        try {
            foreach ($this->places->search($query) as $place) {
                $id = $this->cities->idForPlace($place);
                $value = $id ? 'ville:' . $id : 'lieu:' . $place['ref'];
                $options[$value] ??= $this->option($value, $place['name'], $place['region'], $place['country']);
            }
        } catch (PlaceSearchUnavailable) {
            $unavailable = true;
        }

        return ['options' => array_values($options), 'unavailable' => $unavailable];
    }

    /**
     * The city behind a choice. A city of the world search is looked up again with the search text:
     * the browser only sends its reference, so nobody can save a made-up city.
     * @return ?array{value: string, id: ?int, place: ?array, name: string, detail: string}
     */
    public function resolve(string $choice, string $query): ?array
    {
        [$type, $key] = explode(':', $choice, 2) + ['', ''];
        if ($type === 'ville') {
            $city = ctype_digit($key) ? $this->cities->find((int) $key) : null;

            return $city ? ['id' => $city['id'], 'place' => null] + $this->option($choice, $city['name'], $city['region'], $city['country']) : null;
        }
        $place = $type === 'lieu' ? $this->findPlace($query, $key) : null;
        if ($place === null) {
            return null;
        }
        $id = $this->cities->idForPlace($place);

        return ['id' => $id, 'place' => $place] + $this->option($choice, $place['name'], $place['region'], $place['country']);
    }

    /** Id of a resolved city, saving it first when it comes from the world search. */
    public function save(array $resolved): int
    {
        return $resolved['id'] ?? $this->cities->createFromPlace($resolved['place']);
    }

    private function findPlace(string $query, string $ref): ?array
    {
        if ($query === '' || $ref === '') {
            return null;
        }
        try {
            foreach ($this->places->search($query) as $place) {
                if ($place['ref'] === $ref) {
                    return $place;
                }
            }
        } catch (PlaceSearchUnavailable) {
        }

        return null;
    }

    private function option(string $value, string $name, string $region, string $country): array
    {
        return ['value' => $value, 'name' => $name, 'detail' => implode(' · ', array_filter([$region, $country]))];
    }
}
