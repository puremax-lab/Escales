<?php

namespace Tests\Core;

use App\Core\Request;
use App\Core\Response;
use PHPUnit\Framework\TestCase;

/** The site can be served from a sub-folder, such as https://example.com/escales. */
final class RequestTest extends TestCase
{
    private array $server;

    protected function setUp(): void
    {
        $this->server = $_SERVER;
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->server;
        Request::$basePath = '';
    }

    public function testRemovesTheSubFolderFromThePath(): void
    {
        $request = $this->requestTo('/escales/villes?pays=2');

        $this->assertSame('/escales', Request::$basePath);
        $this->assertSame('/villes?pays=2', $request->uri);
        $this->assertSame('/', $this->requestTo('/escales')->uri);
    }

    public function testLinksAndRedirectsKeepTheSubFolder(): void
    {
        $this->requestTo('/escales/');

        $this->assertSame('/escales/carnet', url('/carnet'));
        $this->assertSame('/escales/connexion', Response::redirect('/connexion')->headers['Location']);
    }

    public function testWorksAtTheRootOfADomain(): void
    {
        $request = $this->requestTo('/villes', '/index.php');

        $this->assertSame('', Request::$basePath);
        $this->assertSame('/villes', $request->uri);
        $this->assertSame('/carnet', url('/carnet'));
    }

    private function requestTo(string $uri, string $script = '/escales/index.php'): Request
    {
        $_SERVER['REQUEST_URI'] = $uri;
        $_SERVER['SCRIPT_NAME'] = $script;
        $_SERVER['REQUEST_METHOD'] = 'GET';

        return Request::fromGlobals();
    }
}
