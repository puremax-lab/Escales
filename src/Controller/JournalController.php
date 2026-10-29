<?php

namespace App\Controller;

use App\Core\Response;
use App\Core\Validator;
use App\Repository\CityRepository;
use App\Repository\VisitRepository;

/** The logged-in member's own journal: list, add, edit and delete stops. */
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

    public function create(CityRepository $cities): Response
    {
        $this->requireUser();
        $old = ['city_id' => $this->request->query('ville'), 'visited_on' => '', 'rating' => '', 'note' => ''];

        return $this->form($cities, null, $old);
    }

    public function store(CityRepository $cities, VisitRepository $visits): Response
    {
        $user = $this->requireUser();
        $v = $this->validate($cities, $visits, $user['id'], null);
        if ($v->fails()) {
            return $this->form($cities, null, $this->request->body, $v->errors());
        }
        $visits->create($user['id'], $this->values($v));

        return $this->redirect('/carnet', 'Escale ajoutée à votre carnet.');
    }

    public function edit(int $id, CityRepository $cities, VisitRepository $visits): Response
    {
        $visit = $this->ownVisit($id, $visits);

        return $this->form($cities, $visit, $visit);
    }

    public function update(int $id, CityRepository $cities, VisitRepository $visits): Response
    {
        $visit = $this->ownVisit($id, $visits);
        $v = $this->validate($cities, $visits, $visit['user_id'], $id);
        if ($v->fails()) {
            return $this->form($cities, $visit, $this->request->body, $v->errors());
        }
        $visits->update($id, $visit['user_id'], $this->values($v));

        return $this->redirect('/carnet', 'Escale modifiée.');
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

    private function validate(CityRepository $cities, VisitRepository $visits, int $userId, ?int $visitId): Validator
    {
        $cityId = (int) $this->request->input('city_id');

        return (new Validator($this->request->body))
            ->required('city_id', 'Choisissez une ville.')
            ->rule('city_id', $cities->exists($cityId), 'Choisissez une ville.')
            ->rule('city_id', !$visits->hasCity($userId, $cityId, $visitId), 'Cette ville est déjà dans votre carnet.')
            ->required('visited_on')
            ->date('visited_on', '1950-01-01', date('Y-m-d'), 'Date invalide ou dans le futur.')
            ->required('rating', 'Donnez une note.')
            ->integer('rating', 1, 5, 'Note de 1 à 5.')
            ->maxLength('note', 500);
    }

    private function values(Validator $v): array
    {
        return [
            'city_id' => (int) $v->value('city_id'),
            'visited_on' => $v->value('visited_on'),
            'rating' => (int) $v->value('rating'),
            'note' => $v->value('note'),
        ];
    }

    private function form(CityRepository $cities, ?array $visit, array $old, array $errors = []): Response
    {
        return $this->render('pages/journal/form', [
            'title' => $visit ? 'Modifier une escale' : 'Ajouter une escale',
            'visit' => $visit,
            'cityGroups' => $cities->groupedByCountry(),
            'old' => $old,
            'errors' => $errors,
        ], $errors ? 422 : 200);
    }
}
