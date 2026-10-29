<?php

namespace App\Controller;

use App\Core\Response;
use App\Core\Validator;
use App\Repository\CityRepository;
use App\Repository\LoginThrottle;
use App\Repository\UserRepository;

final class AuthController extends Controller
{
    /** Hash of a random string, checked when the pseudo does not exist (see login()). */
    private const DUMMY_HASH = '$2y$10$BRsukmfgyhpjorIOWn9MA.WoEoGFOayKuDwi.ZHZAE3g5LYlTGncq';

    public function registerForm(CityRepository $cities): Response
    {
        if ($this->auth->user()) {
            return $this->redirect('/carnet');
        }

        return $this->registerPage($cities);
    }

    public function register(UserRepository $users, CityRepository $cities): Response
    {
        $v = ProfileRules::apply(new Validator($this->request->body), $cities)
            ->required('pseudo')
            ->pattern('pseudo', '/^[a-zA-Z0-9_-]{3,30}$/', 'De 3 à 30 caractères : lettres, chiffres, - et _.')
            ->rule('pseudo', !$users->pseudoExists($this->request->input('pseudo')), 'Ce pseudo est déjà pris.')
            ->required('password')
            ->length('password', 8, 72)
            ->same('password_confirm', 'password', 'Les deux mots de passe ne correspondent pas.');

        if ($v->fails()) {
            return $this->registerPage($cities, $v->errors(), 422);
        }

        $id = $users->create([
            'pseudo' => $v->value('pseudo'),
            'first_name' => $v->value('first_name'),
            'last_name' => $v->value('last_name'),
            'password' => $v->value('password'),
            'birth_date' => $v->value('birth_date'),
            'city_id' => (int) $v->value('city_id'),
        ]);
        $this->auth->login($id);

        return $this->redirect('/carnet', 'Bienvenue ! Ajoutez votre première escale.');
    }

    public function loginForm(): Response
    {
        if ($this->auth->user()) {
            return $this->redirect('/carnet');
        }

        return $this->render('pages/auth/login', ['title' => 'Connexion', 'pseudo' => '']);
    }

    public function login(UserRepository $users, LoginThrottle $throttle): Response
    {
        $pseudo = mb_substr($this->request->input('pseudo'), 0, 30);
        $password = (string) ($this->request->body['password'] ?? '');
        $ip = $this->request->ip;

        if ($throttle->tooManyAttempts($pseudo, $ip)) {
            $error = sprintf('Trop de tentatives. Réessayez dans %d minutes.', LoginThrottle::WINDOW_MINUTES);
        } else {
            $account = $users->findForLogin($pseudo);
            // password_verify runs even for an unknown pseudo, so the answer time does not reveal which pseudos exist.
            $valid = password_verify($password, $account['password_hash'] ?? self::DUMMY_HASH);
            if ($account && $valid) {
                $throttle->clear($pseudo, $ip);
                $users->rehashIfNeeded($account['id'], $password, $account['password_hash']);
                $this->auth->login($account['id']);
                $target = $this->session->get('after_login', '/carnet');
                $this->session->remove('after_login');

                return $this->redirect(str_starts_with($target, '/') ? $target : '/carnet', 'Bon retour parmi nous !');
            }
            $throttle->hit($pseudo, $ip);
            $error = 'Pseudo ou mot de passe incorrect.';
        }

        return $this->render('pages/auth/login', ['title' => 'Connexion', 'pseudo' => $pseudo, 'error' => $error], 422);
    }

    public function logout(): Response
    {
        $this->auth->logout();

        return $this->redirect('/', 'Vous êtes déconnecté.');
    }

    private function registerPage(CityRepository $cities, array $errors = [], int $status = 200): Response
    {
        return $this->render('pages/auth/register', [
            'title' => 'Inscription',
            'cityGroups' => $cities->groupedByCountry(),
            'old' => $this->request->body,
            'errors' => $errors,
        ], $status);
    }
}
