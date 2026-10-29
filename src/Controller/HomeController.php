<?php

namespace App\Controller;

use App\Core\Response;
use App\Repository\CityRepository;
use App\Repository\UserRepository;
use App\Repository\VisitRepository;

final class HomeController extends Controller
{
    public function index(CityRepository $cities, VisitRepository $visits, UserRepository $users): Response
    {
        return $this->render('pages/home', [
            'title' => 'Escales, le carnet de voyage partagé',
            'stats' => ['members' => $users->count(), 'cities' => $cities->count(), 'visits' => $visits->count()],
            'topCities' => $cities->mostVisited(6),
            'latestVisits' => $visits->latest(4),
        ]);
    }
}
