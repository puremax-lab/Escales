<?php

namespace App\Controller;

use App\Core\Response;
use App\Core\Validator;
use App\Repository\CityRepository;
use App\Repository\UserRepository;
use App\Repository\VisitRepository;

final class MemberController extends Controller
{
    /** Public page of a member: profile and journal. */
    public function show(string $pseudo, UserRepository $users, VisitRepository $visits): Response
    {
        $member = $users->findByPseudo($pseudo);
        $this->notFoundUnless($member);

        return $this->render('pages/members/show', [
            'title' => $member['pseudo'],
            'member' => $member,
            'visits' => $visits->forUser($member['id']),
            'stats' => $visits->statsForUser($member['id']),
            'isMe' => $this->auth->id() === $member['id'],
        ]);
    }

    public function edit(CityRepository $cities): Response
    {
        $user = $this->requireUser();

        return $this->form($cities, $user);
    }

    public function update(UserRepository $users, CityRepository $cities): Response
    {
        $user = $this->requireUser();
        $v = ProfileRules::apply(new Validator($this->request->body), $cities);
        if ($v->fails()) {
            return $this->form($cities, $this->request->body, $v->errors());
        }
        $users->updateProfile($user['id'], [
            'first_name' => $v->value('first_name'),
            'last_name' => $v->value('last_name'),
            'birth_date' => $v->value('birth_date'),
            'city_id' => (int) $v->value('city_id'),
        ]);

        return $this->redirect('/membres/' . rawurlencode($user['pseudo']), 'Profil mis à jour.');
    }

    private function form(CityRepository $cities, array $old, array $errors = []): Response
    {
        return $this->render('pages/members/edit', [
            'title' => 'Modifier mon profil',
            'cityGroups' => $cities->groupedByCountry(),
            'old' => $old,
            'errors' => $errors,
        ], $errors ? 422 : 200);
    }
}
