<?php

namespace App\Places;

/** Finds cities anywhere in the world, so members can add any city to their journal. */
interface PlaceFinder
{
    /**
     * @return list<array{ref: string, name: string, region: string, country: string, country_code: string, kind: string}>
     * @throws PlaceSearchUnavailable when the search service does not answer
     */
    public function search(string $query): array;
}
