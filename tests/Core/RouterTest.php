<?php

namespace Tests\Core;

use App\Core\HttpException;
use App\Core\Response;
use App\Core\Router;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        $this->router = new Router();
        $this->router->get('/', fn () => Response::html('home'));
        $this->router->get('/villes/{id}', fn (string $id) => Response::html("city $id"));
        $this->router->get('/membres/{pseudo}', fn (string $pseudo) => Response::html("member $pseudo"));
        $this->router->post('/villes/{id}', fn (string $id) => Response::html("saved $id"));
    }

    public function testMatchesStaticAndParameterRoutes(): void
    {
        $this->assertSame('home', $this->router->dispatch('GET', '/')->body);
        $this->assertSame('city 12', $this->router->dispatch('GET', '/villes/12?x=1')->body);
        $this->assertSame('member léna', $this->router->dispatch('GET', '/membres/l%C3%A9na')->body);
    }

    public function testIgnoresATrailingSlash(): void
    {
        $this->assertSame('city 3', $this->router->dispatch('GET', '/villes/3/')->body);
    }

    public function testChoosesTheRouteByMethod(): void
    {
        $this->assertSame('saved 5', $this->router->dispatch('POST', '/villes/5')->body);
    }

    public function testAnIdOnlyMatchesDigits(): void
    {
        $this->expectExceptionObject(HttpException::notFound());
        $this->router->dispatch('GET', '/villes/abc');
    }

    public function testAnswers405ForAKnownPathWithTheWrongMethod(): void
    {
        try {
            $this->router->dispatch('DELETE', '/villes/5');
            $this->fail('Expected an HttpException');
        } catch (HttpException $e) {
            $this->assertSame(405, $e->status);
        }
    }
}
