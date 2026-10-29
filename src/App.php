<?php

namespace App;

use App\Controller\AuthController;
use App\Controller\CityController;
use App\Controller\HomeController;
use App\Controller\JournalController;
use App\Controller\MemberController;
use App\Controller\RedirectException;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Session;
use App\Core\View;
use App\Repository\CityRepository;
use App\Repository\CountryRepository;
use App\Repository\LoginThrottle;
use App\Repository\UserRepository;
use App\Repository\VisitRepository;
use PDO;

/** Wires the services together, declares the routes and turns a request into a response. */
final class App
{
    private readonly View $view;
    private readonly Csrf $csrf;
    private readonly Auth $auth;
    private readonly CityRepository $cities;
    private readonly CountryRepository $countries;
    private readonly UserRepository $users;
    private readonly VisitRepository $visits;
    private readonly LoginThrottle $throttle;

    public function __construct(
        PDO $pdo,
        private readonly Session $session,
        private readonly bool $debug = false,
    ) {
        $this->cities = new CityRepository($pdo);
        $this->countries = new CountryRepository($pdo);
        $this->users = new UserRepository($pdo);
        $this->visits = new VisitRepository($pdo);
        $this->throttle = new LoginThrottle($pdo);
        $this->csrf = new Csrf($session);
        $this->auth = new Auth($session, $this->users);
        $this->view = new View(dirname(__DIR__) . '/templates');
    }

    public function handle(Request $request): Response
    {
        $this->view->share('currentUser', $this->auth->user());
        $this->view->share('csrfToken', $this->csrf->token());
        $this->view->share('currentPath', parse_url($request->uri, PHP_URL_PATH) ?: '/');
        // Called by the layout, so a redirect does not consume the messages meant for the next page.
        $this->view->share('takeFlashes', fn () => $this->session->takeFlashes());

        try {
            // Every form sends the CSRF token; a POST without a valid one is refused.
            if ($request->method === 'POST' && !$this->csrf->isValid($request->body['_token'] ?? null)) {
                throw new HttpException(419, 'Votre session a expiré. Rechargez la page et réessayez.');
            }

            return $this->router($request)->dispatch($request->method, $request->uri);
        } catch (RedirectException $e) {
            return Response::redirect($e->location);
        } catch (HttpException $e) {
            return $this->errorPage($e->status, $e->getMessage());
        } catch (\Throwable $e) {
            if ($this->debug) {
                throw $e;
            }
            error_log((string) $e);

            return $this->errorPage(500, 'Une erreur est survenue. Réessayez dans un instant.');
        }
    }

    private function router(Request $request): Router
    {
        $home = fn () => new HomeController($request, $this->view, $this->session, $this->auth);
        $city = fn () => new CityController($request, $this->view, $this->session, $this->auth);
        $auth = fn () => new AuthController($request, $this->view, $this->session, $this->auth);
        $journal = fn () => new JournalController($request, $this->view, $this->session, $this->auth);
        $member = fn () => new MemberController($request, $this->view, $this->session, $this->auth);

        $r = new Router();
        $r->get('/', fn () => $home()->index($this->cities, $this->visits, $this->users));

        $r->get('/villes', fn () => $city()->index($this->cities, $this->countries));
        $r->get('/villes/{id}', fn (string $id) => $city()->show((int) $id, $this->cities, $this->visits));

        $r->get('/inscription', fn () => $auth()->registerForm($this->cities));
        $r->post('/inscription', fn () => $auth()->register($this->users, $this->cities));
        $r->get('/connexion', fn () => $auth()->loginForm());
        $r->post('/connexion', fn () => $auth()->login($this->users, $this->throttle));
        $r->post('/deconnexion', fn () => $auth()->logout());

        $r->get('/carnet', fn () => $journal()->index($this->visits));
        $r->get('/carnet/ajouter', fn () => $journal()->create($this->cities));
        $r->post('/carnet/ajouter', fn () => $journal()->store($this->cities, $this->visits));
        $r->get('/carnet/{id}/modifier', fn (string $id) => $journal()->edit((int) $id, $this->cities, $this->visits));
        $r->post('/carnet/{id}/modifier', fn (string $id) => $journal()->update((int) $id, $this->cities, $this->visits));
        $r->get('/carnet/{id}/supprimer', fn (string $id) => $journal()->confirmDelete((int) $id, $this->visits));
        $r->post('/carnet/{id}/supprimer',fn (string $id) => $journal()->delete((int) $id, $this->visits));

        $r->get('/profil/modifier', fn () => $member()->edit($this->cities));
        $r->post('/profil/modifier', fn () => $member()->update($this->users, $this->cities));
        $r->get('/membres/{pseudo}', fn (string $pseudo) => $member()->show($pseudo, $this->users, $this->visits));

        return $r;
    }

    private function errorPage(int $status, string $message): Response
    {
        $titles = [403 => 'Accès refusé', 404 => 'Page introuvable', 405 => 'Méthode non autorisée', 419 => 'Session expirée'];
        $title = $titles[$status] ?? 'Erreur';
        $html = $this->view->render('pages/error', ['title' => $title, 'status' => $status, 'message' => $message]);

        return Response::html($html, $status);
    }
}
