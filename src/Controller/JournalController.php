<?php

namespace App\Controller;

use App\Core\Response;
use App\Core\Validator;
use App\Places\CityPicker;
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
            'title' => t('Mon carnet'),
            'visits' => $visits->forUser($user['id']),
            'stats' => $visits->statsForUser($user['id']),
        ]);
    }

    public function create(CityPicker $picker): Response
    {
        $this->requireUser();
        $old = $this->pickedCity() + ['city_id' => '', 'visited_on' => '', 'rating' => '', 'note' => ''];

        return $this->form($picker, null, $old);
    }

    public function store(CityPicker $picker, VisitRepository $visits): Response
    {
        $user = $this->requireUser();
        [$v, $city] = $this->validate($picker, $visits, $user['id'], null);
        if ($v->fails()) {
            return $this->form($picker, null, $this->request->body, $v->errors());
        }
        $visits->create($user['id'], $this->values($v, $picker->save($city)));

        return $this->redirect('/carnet', t('Escale ajoutée à votre carnet.'));
    }

    public function edit(int $id, CityPicker $picker, VisitRepository $visits): Response
    {
        $visit = $this->ownVisit($id, $visits);

        return $this->form($picker, $visit, $this->pickedCity() + $visit);
    }

    public function update(int $id, CityPicker $picker, VisitRepository $visits): Response
    {
        $visit = $this->ownVisit($id, $visits);
        [$v, $city] = $this->validate($picker, $visits, $visit['user_id'], $id);
        if ($v->fails()) {
            return $this->form($picker, $visit, $this->request->body, $v->errors());
        }
        $visits->update($id, $visit['user_id'], $this->values($v, $picker->save($city)));

        return $this->redirect('/carnet', t('Escale modifiée.'));
    }

    /** Confirmation page: deleting needs a second click on a POST form. */
    public function confirmDelete(int $id, VisitRepository $visits): Response
    {
        $visit = $this->ownVisit($id, $visits);

        return $this->render('pages/journal/delete', ['title' => t('Retirer une escale'), 'visit' => $visit]);
    }

    public function delete(int $id, VisitRepository $visits): Response
    {
        $visit = $this->ownVisit($id, $visits);
        $visits->delete($id, $visit['user_id']);

        return $this->redirect('/carnet', t('%s a été retirée de votre carnet.', t($visit['city'])));
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

    /** @return array{Validator, ?array} the validator and the chosen city */
    private function validate(CityPicker $picker, VisitRepository $visits, int $userId, ?int $visitId): array
    {
        $city = $this->request->input('place') !== ''
            ? $picker->resolve('lieu:' . $this->request->input('place'), $this->request->input('q'))
            : $picker->resolve('ville:' . $this->request->input('city_id'), '');
        $cityId = $city['id'] ?? null;

        $v = (new Validator($this->request->body))
            ->rule('city_id', $city !== null, t('Choisissez une ville.'))
            ->rule('city_id', $cityId === null || !$visits->hasCity($userId, $cityId, $visitId), t('Cette ville est déjà dans votre carnet.'))
            ->required('visited_on')
            ->date('visited_on', '1950-01-01', date('Y-m-d'), t('Date invalide ou dans le futur.'))
            ->required('rating', t('Donnez une note.'))
            ->integer('rating', 1, 5, t('Note de 1 à 5.'))
            ->maxLength('note', 500);

        return [$v, $city];
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

    private function form(CityPicker $picker, ?array $visit, array $old, array $errors = []): Response
    {
        $path = $visit ? '/carnet/' . $visit['id'] . '/modifier' : '/carnet/ajouter';
        $chosen = $this->chosenCity($picker, $old);
        $searching = $chosen === null || $this->request->query('changer') !== '';
        $query = $this->request->query('q') ?: (is_string($old['q'] ?? null) ? $old['q'] : '');

        return $this->render('pages/journal/form', [
            'title' => $visit ? t('Modifier une escale') : t('Ajouter une escale'),
            'visit' => $visit,
            'path' => $path,
            'chosen' => $searching ? null : $chosen,
            'query' => $query,
            'search' => $searching && $query !== '' ? $this->searchResults($picker, $query, $path) : null,
            'old' => $old,
            'errors' => $errors,
        ], $errors ? 422 : 200);
    }

    /** The city already chosen in the form, with the fields to send it again. */
    private function chosenCity(CityPicker $picker, array $old): ?array
    {
        $ref = is_string($old['place'] ?? null) ? $old['place'] : '';
        $query = is_string($old['q'] ?? null) ? $old['q'] : '';
        $city = $ref !== ''
            ? $picker->resolve('lieu:' . $ref, $query)
            : $picker->resolve('ville:' . (is_scalar($old['city_id'] ?? null) ? $old['city_id'] : ''), '');
        if ($city === null) {
            return null;
        }

        return [
            'label' => $city['name'],
            'detail' => $city['detail'],
            'fields' => $ref !== '' ? ['place' => $ref, 'q' => $query] : ['city_id' => (string) $city['id']],
        ];
    }

    /** Each result links back to the form with the city picked. */
    private function searchResults(CityPicker $picker, string $query, string $path): array
    {
        $search = $picker->search($query);
        $results = array_map(function (array $option) use ($path, $query) {
            [$type, $key] = explode(':', $option['value'], 2);
            $params = $type === 'ville' ? ['ville' => $key] : ['lieu' => $key, 'q' => $query];

            return ['href' => $path . '?' . http_build_query($params)] + $option;
        }, $search['options']);

        return ['results' => $results, 'unavailable' => $search['unavailable']];
    }
}
