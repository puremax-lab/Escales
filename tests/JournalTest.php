<?php

namespace Tests;

final class JournalTest extends AppTestCase
{
    private function visit(array $overrides = []): array
    {
        return $overrides + ['city_id' => '20', 'visited_on' => '2025-10-04', 'rating' => '4', 'note' => 'Les traboules du Vieux Lyon.'];
    }

    public function testRequiresALoggedInMember(): void
    {
        $this->assertRedirect($this->get('/carnet'), '/connexion');
        $this->assertRedirect($this->post('/carnet/ajouter', $this->visit()), '/connexion');
    }

    public function testShowsTheMemberStatistics(): void
    {
        $this->loginAs('camille');
        $body = $this->get('/carnet')->body;

        $this->assertStringContainsString('<strong>6</strong><span>villes visitées</span>', $body);
        $this->assertStringContainsString('<strong>5</strong><span>pays</span>', $body);
    }

    public function testAddsAVisit(): void
    {
        $this->loginAs('camille');

        $this->assertRedirect($this->post('/carnet/ajouter', $this->visit()), '/carnet');
        $this->assertSame(1, $this->countRows('SELECT COUNT(*) FROM visits WHERE user_id = 1 AND city_id = 20 AND rating = 4'));
        $this->assertStringContainsString('Escale ajoutée', $this->get('/carnet')->body);
    }

    public function testPreselectsTheCityFromTheCityPage(): void
    {
        $this->loginAs('camille');
        $body = $this->get('/carnet/ajouter?ville=20')->body;

        $this->assertStringContainsString('<strong>Lyon</strong>', $body);
        $this->assertStringContainsString('<input type="hidden" name="city_id" value="20">', $body);
    }

    public function testStartsWithACitySearch(): void
    {
        $this->loginAs('camille');
        $body = $this->get('/carnet/ajouter')->body;

        $this->assertStringContainsString('Quelle ville avez-vous visitée ?', $body);
        $this->assertStringNotContainsString('name="rating"', $body);
    }

    public function testFindsCitiesAnywhereInTheWorld(): void
    {
        $this->loginAs('camille');
        $body = $this->get('/carnet/ajouter?q=Kyo')->body;

        $this->assertStringContainsString('<strong>Kyoto</strong>', $body);
        $this->assertStringContainsString('Préfecture de Kyoto · Japon', $body);
        $this->assertStringContainsString('/carnet/ajouter?lieu=R2689&amp;q=Kyo', $body);
        $this->assertStringContainsString('<strong>Kyotango</strong>', $body);
    }

    public function testSearchShowsASavedCityOnceAndKeepsNamesakes(): void
    {
        $this->loginAs('camille');
        $body = $this->get('/carnet/ajouter?q=Paris')->body;

        // The Paris of the demo data and the Paris of the world search are the same city.
        $this->assertSame(1, substr_count($body, '/carnet/ajouter?ville=19"'));
        $this->assertStringNotContainsString('lieu=R71525', $body);
        $this->assertStringContainsString('Texas · États-Unis', $body);
    }

    public function testAddsACityThatWasNotInEscales(): void
    {
        $this->loginAs('camille');
        $this->assertStringContainsString('<strong>Kyoto</strong>', $this->get('/carnet/ajouter?lieu=R2689&q=Kyo')->body);

        $response = $this->post('/carnet/ajouter', ['place' => 'R2689', 'q' => 'Kyo'] + $this->visit(['city_id' => '']));

        $this->assertRedirect($response, '/carnet');
        $this->assertSame(1, $this->countRows("SELECT COUNT(*) FROM cities ci JOIN countries co ON co.id = ci.country_id
            WHERE ci.name = 'Kyoto' AND ci.region = 'Préfecture de Kyoto' AND ci.place_ref = 'R2689' AND co.name = 'Japon' AND co.code = 'JP'"));
        $this->assertSame(1, $this->countRows("SELECT COUNT(*) FROM visits v JOIN cities ci ON ci.id = v.city_id WHERE v.user_id = 1 AND ci.name = 'Kyoto'"));
        $this->assertStringContainsString('Kyoto', $this->get('/villes?q=Kyoto')->body);
    }

    public function testASecondMemberReusesTheSameCity(): void
    {
        $this->loginAs('camille');
        $this->post('/carnet/ajouter', ['place' => 'R2689', 'q' => 'Kyoto'] + $this->visit(['city_id' => '']));
        $this->loginAs('malik');
        $this->post('/carnet/ajouter', ['place' => 'R2689', 'q' => 'Kyoto'] + $this->visit(['city_id' => '']));

        $this->assertSame(1, $this->countRows("SELECT COUNT(*) FROM cities WHERE place_ref = 'R2689'"));
        $this->assertSame(1, $this->countRows("SELECT COUNT(*) FROM countries WHERE code = 'JP'"));
        $this->assertSame(2, $this->countRows("SELECT COUNT(*) FROM visits v JOIN cities ci ON ci.id = v.city_id WHERE ci.place_ref = 'R2689'"));
    }

    public function testAWorldCityMatchingADemoCityIsNotDuplicated(): void
    {
        $this->loginAs('lena');

        $this->assertRedirect($this->post('/carnet/ajouter', ['place' => 'R71525', 'q' => 'Paris'] + $this->visit(['city_id' => ''])), '/carnet');
        $this->assertSame(1, $this->countRows("SELECT COUNT(*) FROM cities WHERE name = 'Paris'"));
        $this->assertSame(1, $this->countRows("SELECT COUNT(*) FROM visits WHERE user_id = 3 AND city_id = 19"));
    }

    public function testRefusesAMadeUpPlace(): void
    {
        $this->loginAs('camille');

        $response = $this->post('/carnet/ajouter', ['place' => 'R999', 'q' => 'Kyoto', 'city_id' => ''] + $this->visit(['city_id' => '']));

        $this->assertSame(422, $response->status);
        $this->assertStringContainsString('Choisissez une ville.', $response->body);
        $this->assertSame(0, $this->countRows("SELECT COUNT(*) FROM cities WHERE place_ref = 'R999'"));
    }

    public function testStillSearchesSavedCitiesWhenTheWorldSearchIsDown(): void
    {
        $this->loginAs('camille');
        $this->places->down = true;
        $body = $this->get('/carnet/ajouter?q=Lyon')->body;

        $this->assertStringContainsString('<strong>Lyon</strong>', $body);
        $this->assertStringContainsString('La recherche mondiale ne répond pas', $body);
    }

    public function testChangesTheCityOfAVisit(): void
    {
        $this->loginAs('camille');

        $this->assertStringContainsString('Quelle ville avez-vous visitée ?', $this->get('/carnet/1/modifier?changer=1')->body);
        $this->assertRedirect($this->post('/carnet/1/modifier', ['place' => 'R2689', 'q' => 'Kyoto'] + $this->visit(['city_id' => ''])), '/carnet');
        $this->assertSame(1, $this->countRows("SELECT COUNT(*) FROM visits v JOIN cities ci ON ci.id = v.city_id WHERE v.id = 1 AND ci.name = 'Kyoto'"));
    }

    public function testRefusesInvalidVisits(): void
    {
        $this->loginAs('camille');
        $cases = [
            'already in journal' => [['city_id' => '24'], 'déjà dans votre carnet'],
            'unknown city' => [['city_id' => '999'], 'Choisissez une ville.'],
            'future date' => [['visited_on' => date('Y-m-d', strtotime('+1 day'))], 'dans le futur'],
            'rating out of range' => [['rating' => '6'], 'Note de 1 à 5.'],
            'note too long' => [['note' => str_repeat('a', 501)], '500 caractères maximum.'],
        ];

        foreach ($cases as $name => [$overrides, $message]) {
            $response = $this->post('/carnet/ajouter', $this->visit($overrides));
            $this->assertSame(422, $response->status, $name);
            $this->assertStringContainsString($message, $response->body, $name);
        }
        $this->assertSame(6, $this->countRows('SELECT COUNT(*) FROM visits WHERE user_id = 1'));
    }

    public function testEditsAndDeletesOwnVisit(): void
    {
        $this->loginAs('camille');
        $id = 1; // Rome, written by camille

        $this->assertSame(200, $this->get("/carnet/$id/modifier")->status);
        $this->assertRedirect($this->post("/carnet/$id/modifier", $this->visit(['city_id' => '24', 'rating' => '3'])), '/carnet');
        $this->assertSame(3, $this->countRows('SELECT rating FROM visits WHERE id = 1'));

        $this->assertRedirect($this->post("/carnet/$id/supprimer"), '/carnet');
        $this->assertSame(0, $this->countRows('SELECT COUNT(*) FROM visits WHERE id = 1'));
    }

    public function testCannotTouchAnotherMembersVisit(): void
    {
        $this->loginAs('camille');
        $malikVisit = 2; // Yaoundé, written by malik

        $this->assertSame(404, $this->get("/carnet/$malikVisit/modifier")->status);
        $this->assertSame(404, $this->post("/carnet/$malikVisit/modifier", $this->visit())->status);
        $this->assertSame(404, $this->get("/carnet/$malikVisit/supprimer")->status);
        $this->assertSame(404, $this->post("/carnet/$malikVisit/supprimer")->status);
        $this->assertSame(1, $this->countRows('SELECT COUNT(*) FROM visits WHERE id = 2'));
    }

    public function testAsksForConfirmationBeforeDeleting(): void
    {
        $this->loginAs('camille');

        $page = $this->get('/carnet/1/supprimer');

        $this->assertSame(200, $page->status);
        $this->assertStringContainsString('Retirer Rome de votre carnet ?', $page->body);
        $this->assertStringContainsString('<form method="post" action="/carnet/1/supprimer"', $page->body);
        $this->assertSame(1, $this->countRows('SELECT COUNT(*) FROM visits WHERE id = 1'), 'opening the page deletes nothing');
    }

    public function testUpdatesTheProfile(): void
    {
        $this->loginAs('lena');

        $response = $this->post('/profil/modifier', [
            'first_name' => 'Léna',
            'last_name' => 'Martins',
            'birth_date' => '1999-01-25',
            'city_id' => '31', // Lisbonne
        ]);

        $this->assertRedirect($response, '/membres/lena');
        $this->assertStringContainsString('Portugais(e)', $this->get('/membres/lena')->body);
    }

    public function testProfileShowsTheCurrentHomeCityTicked(): void
    {
        $this->loginAs('lena');

        $this->assertStringContainsString('<input type="radio" name="city" value="ville:10" checked>', $this->get('/profil/modifier')->body);
    }

    public function testMovesToACityOutsideEscales(): void
    {
        $this->loginAs('lena');

        $response = $this->post('/profil/modifier', [
            'first_name' => 'Léna',
            'last_name' => 'Martins',
            'birth_date' => '1999-01-25',
            'city' => 'lieu:N4001',
            'city_q' => 'Paris',
        ]);

        $this->assertRedirect($response, '/membres/lena');
        $this->assertStringContainsString('Paris, États-Unis', $this->get('/membres/lena')->body);
    }

    public function testDeletingAMemberDeletesTheirJournal(): void
    {
        $this->pdo->exec('DELETE FROM users WHERE id = 1');

        $this->assertSame(0, $this->countRows('SELECT COUNT(*) FROM visits WHERE user_id = 1'));
    }
}
