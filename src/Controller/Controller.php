<?php

namespace App\Controller;

use App\Core\Auth;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;

abstract class Controller
{
    public function __construct(
        protected readonly Request $request,
        protected readonly View $view,
        protected readonly Session $session,
        protected readonly Auth $auth,
    ) {
    }

    protected function render(string $template, array $data = [], int $status = 200): Response
    {
        return Response::html($this->view->render($template, $data), $status);
    }

    protected function redirect(string $location, ?string $success = null): Response
    {
        if ($success !== null) {
            $this->session->flash('success', $success);
        }

        return Response::redirect($location);
    }

    /** Returns the logged-in user, or stops with a redirect to the login page. */
    protected function requireUser(): array
    {
        $user = $this->auth->user();
        if ($user === null) {
            $this->session->flash('info', 'Connectez-vous pour accéder à cette page.');
            $this->session->set('after_login', $this->request->uri);
            throw new RedirectException('/connexion');
        }

        return $user;
    }

    protected function notFoundUnless(mixed $value): void
    {
        if (!$value) {
            throw HttpException::notFound();
        }
    }
}
