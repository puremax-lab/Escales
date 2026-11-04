<?php

namespace App\Controller;

use App\Core\Request;
use App\Core\Validator;
use App\Places\CityPicker;

/** Rules shared by the sign-up form and the profile form. */
final class ProfileRules
{
    /** @param ?array $city the home city, from homeCity() */
    public static function apply(Validator $v, ?array $city): Validator
    {
        $today = new \DateTimeImmutable('today');

        return $v
            ->required('first_name')->length('first_name', 2, 50)
            ->required('last_name')->length('last_name', 2, 50)
            ->required('birth_date')
            ->date(
                'birth_date',
                $today->modify('-120 years')->format('Y-m-d'),
                $today->modify('-13 years')->format('Y-m-d'),
                t('Date invalide : il faut avoir au moins 13 ans.'),
            )
            ->rule('city', $city !== null, t('Choisissez votre ville.'));
    }

    /** The home city ticked in the list ("city"), or sent as a plain id ("city_id"). */
    public static function homeCity(Request $request, CityPicker $picker): ?array
    {
        $choice = $request->input('city');
        if ($choice === '' && $request->input('city_id') !== '') {
            $choice = 'ville:' . $request->input('city_id');
        }

        return $choice === '' ? null : $picker->resolve($choice, $request->input('city_q'));
    }

    /**
     * What the city field shows: the search text, the cities found and the one ticked.
     * The "Chercher" button sends the whole form again with find_city=1, so it works without JavaScript.
     */
    public static function cityField(Request $request, CityPicker $picker, ?array $chosen): array
    {
        $query = mb_substr($request->input('city_q'), 0, 80);
        $search = $query !== '' && ($request->input('find_city') !== '' || $chosen === null)
            ? $picker->search($query)
            : ['options' => [], 'unavailable' => false];
        $options = $search['options'];
        if ($chosen !== null && !in_array($chosen['value'], array_column($options, 'value'), true)) {
            array_unshift($options, $chosen);
        }

        return [
            'query' => $query,
            'options' => $options,
            'checked' => $chosen['value'] ?? '',
            'unavailable' => $search['unavailable'],
            'searched' => $query !== '' && ($request->input('find_city') !== '' || $chosen === null),
        ];
    }
}
