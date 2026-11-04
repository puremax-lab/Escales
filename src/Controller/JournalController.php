<?php

namespace App\Controller;

use App\Core\Response;
use App\Core\Validator;
use App\Places\PlaceFinder;
use App\Places\PlaceSearchUnavailable;
use App\Repository\CityRepository;
use App\Repository\VisitRepository;

/**
 * The logged-in member's own journal: list, add, edit and delete stops.
 * A stop is added in two steps: find the city (anywhere in the world), then rate it.
 */
final class JournalController extends Controller
{
    public function index(VisitRepository $visits): Response
    {
        $user = $this->requireUser();

        return $this->render('pages/journal/index', [
            'title' => 'Mon carnet',
            'visits' => $visits->forUser($user['id']),
            'stats' => $visits->statsForUser($user['id']),
        ]);
    }

    public function create(CityRepository $cities, PlaceFinder $places): Response
    {
        $this->requireUser();
        $old = $this->pickedCity() + ['city_id' => '', 'visited_on' => '', 'rating' => '', 'note' => ''];

        return $this->form($cities, $places, null, $old);
    }

    public function store(CityRepository $cities, PlaceFinder $places, VisitRepository $visits): Response
    {
        $user = $this->requireUser();
        [$v, $cityId, $place] = $this->validate($cities, $places, $visits, $user['id'], null);
        if ($v->fails()) {
            return $this->form($cities, $places, null, $this->request->body, $v->errors());
        }
        $visits->create($user['id'], $this->values($v, $cityId ?? $cities->createFromPlace($place)));

        return $this->redirect('/carnet', 'Escale ajoutée à votre carnet.');
    }

    public function edit(int $id, CityRepository $cities, PlaceFinder $places, VisitRepository $visits): Response
    {
        $visit = $this->ownVisit($id, $visits);

        return $this->form($cities, $places, $visit, $this->pickedCity() + $visit);
    }

    public function update(int $id, CityRepository $cities, PlaceFinder $places, VisitRepository $visits): Response
    {
        $visit = $this->ownVisit($id, $visits);
        [$v, $cityId, $place] = $this->validate($cities, $places, $visits, $visit['user_id'], $id);
        if ($v->fails()) {
            return $this->form($cities, $places, $visit, $this->request->body, $v->errors());
        }
        $visits->update($id, $visit['user_id'], $this->values($v, $cityId ?? $cities->createFromPlace($place)));

        return $this->redirect('/carnet', 'Escale modifiée.');
    }

    /** Confirmation page: deleting needs a second click on a POST form. */
    public function confirmDelete(int $id, VisitRepository $visits): Response
    {
        $visit = $this->ownVisit($id, $visits);

        return $this->render('pages/journal/delete', ['title' => 'Retirer une escale', 'visit' => $visit]);
    }

    public function delete(int $id, VisitRepository $visits): Response
    {
        $visit = $this->ownVisit($id, $visits);
        $visits->delete($id, $visit['user_id']);

        return $this->redirect('/carnet', sprintf('%s a été retirée de votre carnet.', $visit['city']));
    }

    /** A visit of the logged-in member; someone else's visit is answered with 404. */
    private function ownVisit(int $id, VisitRepository $visits): array
    {
        $user = $this->requireUser();
        $visit = $visits->find($id);
        $this->notFoundUnless($visit && $visit['user_id'] === $user['id']);

        return $visit;
    }

    /** The city picked in the search results: ?ville= for a saved city, ?lieu= and ?q= for a new one. */
    private function pickedCity(): array
    {
        if ($this->request->query('ville') !== '') {
            return ['city_id' => $this->request->query('ville'), 'place' => '', 'q' => ''];
        }
        if ($this->request->query('lieu') !== '') {
            return ['city_id' => '', 'place' => $this->request->query('lieu'), 'q' => $this->request->query('q')];
        }

        return [];
    }

    /**
     * A city of the world search, looked up again on the server: the browser only sends its reference,
     * so nobody can save a made-up city.
     */
    private function findPlace(PlaceFinder $places, string $query, string $ref): ?array
    {
        if ($query === '' || $ref === '') {
            return null;
        }
        try {
            foreach ($places->search($query) as $place) {
                if ($place['ref'] === $ref) {
                    return $place;
                }
            }
        } catch (PlaceSearchUnavailable) {
        }

        return null;
    }

    /** @return array{Validator, ?int, ?array} the validator, and the saved city or the new place */
    private function validate(CityRepository $cities, PlaceFinder $places, VisitRepository $visits, int $userId, ?int $visitId): array
    {
        $place = null;
        $cityId = (int) $this->request->input('city_id');
        $cityId = $cities->exists($cityId) ? $cityId : null;
        if ($this->request->input('place') !== '') {
            $place = $this->findPlace($places, $this->request->input('q'), $this->request->input('place'));
            $cityId = $place ? $cities->idForPlace($place) : null;
        }

        $v = (new Validator($this->request->body))
            ->rule('city_id', $cityId !== null || $place !== null, 'Choisissez une ville.')
            ->rule('city_id', $cityId === null || !$visits->hasCity($userId, $cityId, $visitId), 'Cette ville est déjà dans votre carnet.')
            ->required('visited_on')
            ->date('visited_on', '1950-01-01', date('Y-m-d'), 'Date invalide ou dans le futur.')
            ->required('rating', 'Donnez une note.')
            ->integer('rating', 1, 5, 'Note de 1 à 5.')
            ->maxLength('note', 500);

        return [$v, $cityId, $place];
    }

    private function values(Validator $v, int $cityId): array
    {
        return [
            'city_id' => $cityId,
            'visited_on' => $v->value('visited_on'),
            'rating' => (int) $v->value('rating'),
            'note' => $v->value('note'),
        ];
    }

    private function form(CityRepository $cities, PlaceFinder $places, ?array $visit, array $old, array $errors = []): Response
    {
        $path = $visit ? '/carnet/' . $visit['id'] . '/modifier' : '/carnet/ajouter';
        $chosen = $this->chosenCity($cities, $places, $old);
        $searching = $chosen === null || $this->request->query('changer') !== '';
        $query = $this->request->query('q') ?: (string) ($old['q'] ?? '');

        return $this->render('pages/journal/form', [
            'title' => $visit ? 'Modifier une escale' : 'Ajouter une escale',
            'visit' => $visit,
            'path' => $path,
            'chosen' => $searching ? null : $chosen,
            'query' => $query,
            'search' => $searching && $query !== '' ? $this->searchResults($cities, $places, $query, $path) : null,
            'old' => $old,
            'errors' => $errors,
        ], $errors ? 422 : 200);
    }

    /** The city already chosen in the form, with the fields to send it again. */
    private function chosenCity(CityRepository $cities, PlaceFinder $places, array $old): ?array
    {
        $ref = is_string($old['place'] ?? null) ? $old['place'] : '';
        if ($ref !== '') {
            $query = is_string($old['q'] ?? null) ? $old['q'] : '';
            $place = $this->findPlace($places, $query, $ref);

            return $place ? [
                'label' => $place['name'],
                'detail' => implode(' · ', array_filter([$place['region'], $place['country']])),
                'fields' => ['place' => $ref, 'q' => $query],
            ] : null;
        }

        $city = is_scalar($old['city_id'] ?? null) ? $cities->find((int) $old['city_id']) : null;

        return $city ? [
            'label' => $city['name'],
            'detail' => implode(' · ', array_filter([$city['region'], $city['country']])),
            'fields' => ['city_id' => (string) $city['id']],
        ] : null;
    }

    /** Cities already in Escales first, then the rest of the world. */
    private function searchResults(CityRepository $cities, PlaceFinder $places, string $query, string $path): array
    {
        $results = [];
        foreach ($cities->search($query, null, 8, 0) as $city) {
            $results[$city['id']] = [
                'href' => $path . '?ville=' . $city['id'],
                'name' => $city['name'],
                'detail' => implode(' · ', array_filter([$city['region'], $city['country']])),
            ];
        }

        $unavailable = false;
        try {
            foreach ($places->search($query) as $place) {
                $id = $cities->idForPlace($place);
                $key = $id ?? $place['ref'];
                if (isset($results[$key])) {
                    continue;
                }
                $results[$key] = [
                    'href' => $path . '?' . ($id ? 'ville=' . $id : http_build_query(['lieu' => $place['ref'], 'q' => $query])),
                    'name' => $place['name'],
                    'detail' => implode(' · ', array_filter([$place['region'], $place['country']])),
                ];
            }
        } catch (PlaceSearchUnavailable) {
            $unavailable = true;
        }

        return ['results' => array_values($results), 'unavailable' => $unavailable];
    }
}
