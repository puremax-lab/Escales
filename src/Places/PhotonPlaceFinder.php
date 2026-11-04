<?php

namespace App\Places;

/**
 * City search with Photon, a free geocoder built on OpenStreetMap data (https://photon.komoot.io).
 * Names come back in French: "Londres", "Pékin", "Le Caire".
 */
final class PhotonPlaceFinder implements PlaceFinder
{
    public function __construct(private readonly string $endpoint = 'https://photon.komoot.io/api/')
    {
    }

    public function search(string $query): array
    {
        $url = $this->endpoint . '?' . http_build_query(['q' => $query, 'lang' => 'fr', 'limit' => 10, 'layer' => 'city']);
        $context = stream_context_create(['http' => [
            'timeout' => 4,
            'header' => "User-Agent: Escales travel journal (https://github.com/puremax-lab/Escales)\r\n",
        ]]);
        $body = @file_get_contents($url, false, $context);
        $data = is_string($body) ? json_decode($body, true) : null;
        if (!is_array($data) || !is_array($data['features'] ?? null)) {
            throw new PlaceSearchUnavailable('The city search did not answer.');
        }

        $places = [];
        foreach ($data['features'] as $feature) {
            $place = $this->place($feature['properties'] ?? []);
            // The same town can come back twice (the town and its municipality): keep the first one.
            $key = $place ? mb_strtolower($place['name'] . '|' . $place['region'] . '|' . $place['country_code']) : null;
            if ($place && !isset($places[$key])) {
                $places[$key] = $place;
            }
        }

        return array_values($places);
    }

    private function place(array $p): ?array
    {
        foreach (['osm_type', 'osm_id', 'name', 'country', 'countrycode'] as $key) {
            if (!is_scalar($p[$key] ?? null) || (string) $p[$key] === '') {
                return null;
            }
        }

        return [
            'ref' => $p['osm_type'] . $p['osm_id'],
            'name' => mb_substr((string) $p['name'], 0, 80),
            'region' => mb_substr((string) ($p['state'] ?? $p['county'] ?? ''), 0, 80),
            'country' => mb_substr((string) $p['country'], 0, 60),
            'country_code' => strtoupper(substr((string) $p['countrycode'], 0, 2)),
            'kind' => (string) ($p['osm_value'] ?? ''),
        ];
    }
}
