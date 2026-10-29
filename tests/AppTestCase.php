<?php

namespace Tests;

use App\App;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Runs requests through the whole application, with an in-memory SQLite database
 * filled with the demo data and a session kept in a plain array.
 */
abstract class AppTestCase extends TestCase
{
    protected PDO $pdo;
    protected array $sessionData = [];

    protected function setUp(): void
    {
        $this->pdo = Database::connect('sqlite::memory:');
        Database::install($this->pdo);
        $this->sessionData = [];
    }

    protected function get(string $uri): Response
    {
        $query = [];
        parse_str((string) parse_url($uri, PHP_URL_QUERY), $query);

        return $this->app()->handle(new Request('GET', $uri, $query));
    }

    /** Sends a form, with the session's CSRF token unless one is given. */
    protected function post(string $uri, array $data = [], string $ip = '127.0.0.1'): Response
    {
        $data += ['_token' => $this->csrfToken()];

        return $this->app()->handle(new Request('POST', $uri, [], $data, $ip));
    }

    protected function loginAs(string $pseudo, string $password = 'password'): Response
    {
        return $this->post('/connexion', ['pseudo' => $pseudo, 'password' => $password]);
    }

    protected function csrfToken(): string
    {
        // Rendering any page creates the token.
        if (!isset($this->sessionData['_csrf'])) {
            $this->get('/connexion');
        }

        return $this->sessionData['_csrf'];
    }

    protected function assertRedirect(Response $response, string $location): void
    {
        $this->assertSame(303, $response->status);
        $this->assertSame($location, $response->headers['Location']);
    }

    protected function countRows(string $sql, array $params = []): int
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    private function app(): App
    {
        return new App($this->pdo, new Session($this->sessionData), debug: true);
    }
}
