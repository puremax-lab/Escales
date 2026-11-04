<?php

namespace App\Controller;

use App\Core\Response;
use App\Core\Validator;
use App\Places\CityPicker;
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

    public function edit(CityPicker $picker): Response
    {
        $user = $this->requireUser();

        return $this->form($picker, $user, $picker->resolve('ville:' . $user['city_id'], ''));
    }

    public function update(UserRepository $users, CityPicker $picker): Response
    {
        $user = $this->requireUser();
        $city = ProfileRules::homeCity($this->request, $picker);
        if ($this->request->input('find_city') !== '') {
            return $this->form($picker, $this->request->body, $city);
        }
        $v = ProfileRules::apply(new Validator($this->request->body), $city);
        if ($v->fails()) {
            return $this->form($picker, $this->request->body, $city, $v->errors());
        }
        $users->updateProfile($user['id'], [
            'first_name' => $v->value('first_name'),
            'last_name' => $v->value('last_name'),
            'birth_date' => $v->value('birth_date'),
            'city_id' => $picker->save($city),
        ]);

        return $this->redirect('/membres/' . rawurlencode($user['pseudo']), t('Profil mis à jour.'));
    }

    private function form(CityPicker $picker, array $old, ?array $city, array $errors = []): Response
    {
        return $this->render('pages/members/edit', [
            'title' => t('Modifier mon profil'),
            'cityField' => ProfileRules::cityField($this->request, $picker, $city),
            'old' => $old,
            'errors' => $errors,
        ], $errors ? 422 : 200);
    }
}
