<?php

namespace App\Controller;

use App\Core\Lang;
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

        // Names are stored in French: an English name is looked up as its French version.
        $search = Lang::toFrench($query);
        $total = $cities->countSearch($search, $countryId);
        $pages = max(1, (int) ceil($total / self::PER_PAGE));
        $page = min($pages, max(1, (int) $this->request->query('page')));

        return $this->render('pages/cities/index', [
            'title' => t('Villes'),
            'cities' => $cities->search($search, $countryId, self::PER_PAGE, ($page - 1) * self::PER_PAGE),
            'countries' => $this->sortedByName($countries->all()),
            'query' => $query,
            'countryId' => $countryId,
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
            'pageLinks' => self::pageLinks($page, $pages),
        ]);
    }

    /**
     * Page numbers to show: the first, the last, and two on each side of the current one.
     * A gap between them is marked with null ("…").
     * @return list<?int>
     */
    public static function pageLinks(int $page, int $pages): array
    {
        $links = [];
        foreach (range(1, $pages) as $n) {
            if ($n === 1 || $n === $pages || abs($n - $page) <= 2) {
                if ($links && end($links) !== null && end($links) < $n - 1) {
                    $links[] = null;
                }
                $links[] = $n;
            }
        }

        return $links;
    }

    /** The database sorts countries by their French name; English needs its own order. */
    private function sortedByName(array $countries): array
    {
        usort($countries, fn (array $a, array $b) => strcmp(t($a['name']), t($b['name'])));

        return $countries;
    }

    public function show(int $id, CityRepository $cities, VisitRepository $visits): Response
    {
        $city = $cities->find($id);
        $this->notFoundUnless($city);
        $userId = $this->auth->id();

        return $this->render('pages/cities/show', [
            'title' => t($city['name']),
            'city' => $city,
            'visits' => $visits->forCity($id, 20),
            'inJournal' => $userId !== null && $visits->hasCity($userId, $id),
        ]);
    }
}
