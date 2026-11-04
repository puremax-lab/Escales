<?php

namespace Tests;

use App\Core\Lang;
use PHPUnit\Framework\Attributes\DataProvider;

final class LanguageTest extends AppTestCase
{
    protected function tearDown(): void
    {
        Lang::set('fr');
    }

    private function inEnglish(): void
    {
        $this->sessionData[Lang::SESSION_KEY] = 'en';
    }

    public function testFrenchIsTheDefault(): void
    {
        $body = $this->get('/')->body;

        $this->assertStringContainsString('<html lang="fr">', $body);
        $this->assertStringContainsString('Gardez une trace de chaque escale.', $body);
    }

    public function testTheHeaderHasLinksToBothLanguagesThatReturnToThePage(): void
    {
        $body = $this->get('/villes?q=lisb')->body;

        $this->assertStringContainsString('href="/langue/fr?retour=%2Fvilles%3Fq%3Dlisb"', $body);
        $this->assertStringContainsString('href="/langue/en?retour=%2Fvilles%3Fq%3Dlisb"', $body);
    }

    public function testSwitchingLanguageReturnsToThePageAndIsRemembered(): void
    {
        $this->assertRedirect($this->get('/langue/en?retour=%2Fvilles%3Fq%3Dlisb'), '/villes?q=lisb');

        $body = $this->get('/')->body;
        $this->assertStringContainsString('<html lang="en">', $body);
        $this->assertStringContainsString('Keep track of every stop.', $body);
        $this->assertStringNotContainsString('Gardez une trace', $body);

        $this->get('/langue/fr');
        $this->assertStringContainsString('<html lang="fr">', $this->get('/')->body);
    }

    public function testAnUnknownLanguageIsAnError(): void
    {
        $this->assertSame(404, $this->get('/langue/de')->status);
    }

    #[DataProvider('foreignTargets')]
    public function testNeverRedirectsToAnotherSite(string $target): void
    {
        $this->assertRedirect($this->get('/langue/en?retour=' . rawurlencode($target)), '/');
    }

    public static function foreignTargets(): array
    {
        return [['https://evil.example'], ['//evil.example'], ['/\\evil.example'], ['']];
    }

    public function testTranslatesFormErrors(): void
    {
        $this->inEnglish();

        $body = $this->post('/inscription', ['pseudo' => 'x'])->body;

        $this->assertStringContainsString('This field is required.', $body);
        $this->assertStringContainsString('3 to 30 characters', $body);
    }

    public function testTranslatesLoginMessagesAndErrorPages(): void
    {
        $this->inEnglish();

        $this->assertStringContainsString('Wrong username or password.', $this->loginAs('camille', 'wrong')->body);
        $notFound = $this->get('/nowhere');
        $this->assertSame(404, $notFound->status);
        $this->assertStringContainsString('Page not found · Escales', $notFound->body);
    }

    public function testTranslatesTheDemoCitiesCountriesReviewsAndDates(): void
    {
        $this->inEnglish();

        $body = $this->get('/membres/camille')->body;
        $this->assertStringContainsString('Brussels, Belgium', $body);
        $this->assertStringContainsString('Belgian', $body);
        $this->assertStringContainsString('Three days are not enough.', $body);
        $this->assertStringContainsString('Visited on 3 October 2024', $body);
        $this->assertStringContainsString('years old', $body);
    }

    public function testSearchesCitiesByTheirEnglishName(): void
    {
        $this->inEnglish();

        $body = $this->get('/villes?q=brussels')->body;
        $this->assertStringContainsString('Brussels', $body);
        $this->assertStringContainsString('1 city', $body);

        $this->assertStringContainsString('Vienna', $this->get('/villes?q=austria')->body);
    }

    public function testKeepsWhatAMemberWrote(): void
    {
        $this->assertSame('Un avis écrit à la main.', t('Un avis écrit à la main.'));
        Lang::set('en');
        $this->assertSame('Un avis écrit à la main.', t('Un avis écrit à la main.'));
    }

    public function testTheLegalNoticeHasAnEnglishVersion(): void
    {
        $this->inEnglish();

        $this->assertStringContainsString('<h1>Legal notice</h1>', $this->get('/mentions-legales')->body);
    }

    public function testKeepsTheLanguageAfterLoggingOut(): void
    {
        $this->inEnglish();
        $this->loginAs('camille');

        $this->post('/deconnexion');

        $body = $this->get('/')->body;
        $this->assertStringContainsString('You are logged out.', $body);
        $this->assertStringContainsString('<html lang="en">', $body);
    }

    public function testEveryTextInTheCodeHasAnEnglishVersion(): void
    {
        $root = dirname(__DIR__);
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root . '/templates', \FilesystemIterator::SKIP_DOTS));
        $paths = array_map('strval', iterator_to_array($files, false));
        $paths = array_merge($paths, glob($root . '/src/*.php'), glob($root . '/src/*/*.php'));

        $missing = [];
        foreach ($paths as $path) {
            preg_match_all('/\bt\(\s*(\'(?:[^\'\\\\]|\\\\.)*\'|"[^"$]*")/u', file_get_contents($path), $calls);
            foreach ($calls[1] as $literal) {
                $text = stripcslashes(substr($literal, 1, -1));
                if (!isset(Lang::english()[$text])) {
                    $missing[] = $text;
                }
            }
        }

        $this->assertSame([], $missing);
    }
}
