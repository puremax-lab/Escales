<?php

namespace App\Controller;

use App\Core\Validator;
use App\Repository\CityRepository;

/** Rules shared by the sign-up form and the profile form. */
final class ProfileRules
{
    public static function apply(Validator $v, CityRepository $cities): Validator
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
                'Date invalide : il faut avoir au moins 13 ans.',
            )
            ->required('city_id', 'Choisissez votre ville.')
            ->rule('city_id', $cities->exists((int) $v->value('city_id')), 'Choisissez votre ville.');
    }
}
