<?php

namespace App\Controller;

use App\Core\Response;
use App\Repository\CityRepository;
use App\Repository\CountryRepository;
use App\Repository\VisitRepository;

final class CityController extends Controller
{
    private const PER_PAGE = 12;

    public function index(CityRepository $cities, CountryRepository $countries): Response
    {
        $query = mb_substr($this->request->query('q'), 0, 80);
        $countryId = filter_var($this->request->query('pays'), FILTER_VALIDATE_INT) ?: null;

        $total = $cities->countSearch($query, $countryId);
        $pages = max(1, (int) ceil($total / self::PER_PAGE));
        $page = min($pages, max(1, (int) $this->request->query('page')));

        return $this->render('pages/cities/index', [
            'title' => 'Villes',
            'cities' => $cities->search($query, $countryId, self::PER_PAGE, ($page - 1) * self::PER_PAGE),
            'countries' => $countries->all(),
            'query' => $query,
            'countryId' => $countryId,
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
        ]);
    }

    public function show(int $id, CityRepository $cities, VisitRepository $visits): Response
    {
        $city = $cities->find($id);
        $this->notFoundUnless($city);
        $userId = $this->auth->id();

        return $this->render('pages/cities/show', [
            'title' => $city['name'],
            'city' => $city,
            'visits' => $visits->forCity($id, 20),
            'inJournal' => $userId !== null && $visits->hasCity($userId, $id),
        ]);
    }
}
