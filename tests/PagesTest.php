<?php

namespace Tests;

final class PagesTest extends AppTestCase
{
    public function testHomePageShowsFiguresAndMostVisitedCities(): void
    {
        $response = $this->get('/');

        $this->assertSame(200, $response->status);
        $this->assertStringContainsString('<strong>3</strong><span>voyageurs</span>', $response->body);
        $this->assertStringContainsString('Lisbonne', $response->body);
    }

    public function testSearchesCitiesByCityOrCountryName(): void
    {
        $byCity = $this->get('/villes?q=lisb')->body;
        $this->assertStringContainsString('Lisbonne', $byCity);
        $this->assertStringContainsString('1 ville', $byCity);

        $byCountry = $this->get('/villes?q=Portugal')->body;
        $this->assertStringContainsString('2 villes', $byCountry);
    }

    public function testTreatsLikeWildcardsAsPlainText(): void
    {
        $this->assertStringContainsString('0 ville', $this->get('/villes?q=%25')->body);
    }

    public function testFiltersCitiesByCountry(): void
    {
        $body = $this->get('/villes?pays=3')->body; // Belgique

        $this->assertStringContainsString('6 villes', $body);
        $this->assertStringNotContainsString('Lisbonne', $body);
    }

    public function testPaginatesTheCityList(): void
    {
        $first = $this->get('/villes')->body;
        $this->assertSame(12, substr_count($first, 'class="card city-card"'));
        $this->assertStringContainsString('href="/villes?page=3"', $first);

        $last = $this->get('/villes?page=3')->body;
        $this->assertSame(11, substr_count($last, 'class="card city-card"'));
    }

    public function testCityPageShowsReviewsAndAverageRating(): void
    {
        $body = $this->get('/villes/24')->body; // Rome

        $this->assertStringContainsString('4,5 / 5', $body);
        $this->assertStringContainsString('Le Vatican mérite une journée entière.', $body);
    }

    public function testMemberPageShowsProfileAndNationality(): void
    {
        $body = $this->get('/membres/malik')->body;

        $this->assertStringContainsString('Malik Ekotto', $body);
        $this->assertStringContainsString('Camerounais(e)', $body);
        $this->assertStringContainsString('Kribi', $body);
    }

    public function testUnknownPagesAnswer404(): void
    {
        $this->assertSame(404, $this->get('/villes/999')->status);
        $this->assertSame(404, $this->get('/membres/personne')->status);
        $this->assertSame(404, $this->get('/nulle-part')->status);
    }

    public function testEscapesUserContent(): void
    {
        $this->pdo->exec("UPDATE visits SET note = '<script>alert(1)</script>' WHERE id = 1");

        $body = $this->get('/villes/24')->body;

        $this->assertStringNotContainsString('<script>alert(1)</script>', $body);
        $this->assertStringContainsString('&lt;script&gt;', $body);
    }
}
