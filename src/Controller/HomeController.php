<?php

namespace App\Controller;

use App\Core\HttpException;
use App\Core\Lang;
use App\Core\Response;
use App\Repository\CityRepository;
use App\Repository\UserRepository;
use App\Repository\VisitRepository;

final class HomeController extends Controller
{
    public function index(CityRepository $cities, VisitRepository $visits, UserRepository $users): Response
    {
        return $this->render('pages/home', [
            'title' => t('Escales, le carnet de voyage partagé'),
            'stats' => ['members' => $users->count(), 'cities' => $cities->count(), 'visits' => $visits->count()],
            'topCities' => $cities->mostVisited(6),
            'latestVisits' => $visits->latest(4),
        ]);
    }

    public function legal(): Response
    {
        return $this->render(Lang::$locale === 'en' ? 'pages/legal.en' : 'pages/legal', ['title' => t('Mentions légales')]);
    }

    /** Link of the language switch: keeps the choice in the session and goes back to the page. */
    public function language(string $locale): Response
    {
        if (!in_array($locale, Lang::LOCALES, true)) {
            throw HttpException::notFound();
        }
        $this->session->set(Lang::SESSION_KEY, $locale);
        $target = $this->request->query('retour');

        // Only a path of this site: "//other.site" or "/\\other.site" would leave it.
        return $this->redirect(preg_match('#^/(?![/\\\\])#', $target) === 1 ? $target : '/');
    }
}
