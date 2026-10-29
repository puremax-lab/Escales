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

        $this->assertStringContainsString('<option value="20" selected>Lyon</option>', $this->get('/carnet/ajouter?ville=20')->body);
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

    public function testDeletingAMemberDeletesTheirJournal(): void
    {
        $this->pdo->exec('DELETE FROM users WHERE id = 1');

        $this->assertSame(0, $this->countRows('SELECT COUNT(*) FROM visits WHERE user_id = 1'));
    }
}
