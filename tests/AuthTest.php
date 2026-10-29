<?php

namespace Tests;

use App\Repository\LoginThrottle;

final class AuthTest extends AppTestCase
{
    private function validSignUp(array $overrides = []): array
    {
        return $overrides + [
            'first_name' => 'Anna',
            'last_name' => 'Vermeulen',
            'pseudo' => 'anna_v',
            'birth_date' => '1998-06-15',
            'city_id' => '8',
            'password' => 'unmotdepasse',
            'password_confirm' => 'unmotdepasse',
        ];
    }

    public function testSignsUpAndLogsTheNewMemberIn(): void
    {
        $response = $this->post('/inscription', $this->validSignUp());

        $this->assertRedirect($response, '/carnet');
        $this->assertSame(1, $this->countRows("SELECT COUNT(*) FROM users WHERE pseudo = 'anna_v'"));
        $this->assertSame(200, $this->get('/carnet')->status);
    }

    public function testStoresAHashNotThePassword(): void
    {
        $this->post('/inscription', $this->validSignUp());

        $hash = $this->pdo->query("SELECT password_hash FROM users WHERE pseudo = 'anna_v'")->fetchColumn();
        $this->assertNotSame('unmotdepasse', $hash);
        $this->assertTrue(password_verify('unmotdepasse', $hash));
    }

    public function testRefusesInvalidSignUps(): void
    {
        $cases = [
            'pseudo taken' => [['pseudo' => 'Camille'], 'Ce pseudo est déjà pris.'],
            'bad pseudo' => [['pseudo' => 'a b'], 'lettres, chiffres'],
            'short password' => [['password' => 'court', 'password_confirm' => 'court'], 'Entre 8 et 72 caractères.'],
            'passwords differ' => [['password_confirm' => 'autrechose'], 'ne correspondent pas'],
            'too young' => [['birth_date' => date('Y-m-d', strtotime('-10 years'))], 'au moins 13 ans'],
            'unknown city' => [['city_id' => '999'], 'Choisissez votre ville.'],
        ];

        foreach ($cases as $name => [$overrides, $message]) {
            $response = $this->post('/inscription', $this->validSignUp($overrides));
            $this->assertSame(422, $response->status, $name);
            $this->assertStringContainsString($message, $response->body, $name);
        }
        $this->assertSame(3, $this->countRows('SELECT COUNT(*) FROM users'));
    }

    public function testKeepsTheTypedValuesWhenTheFormIsInvalid(): void
    {
        $body = $this->post('/inscription', $this->validSignUp(['pseudo' => '']))->body;

        $this->assertStringContainsString('value="Vermeulen"', $body);
        $this->assertStringNotContainsString('unmotdepasse', $body, 'passwords are never sent back');
    }

    public function testLogsInAndOut(): void
    {
        $this->assertRedirect($this->loginAs('camille'), '/carnet');
        $this->assertStringContainsString('Mon carnet', $this->get('/carnet')->body);

        $this->assertRedirect($this->post('/deconnexion'), '/');
        $this->assertRedirect($this->get('/carnet'), '/connexion');
    }

    public function testRefusesAWrongPasswordWithAGenericMessage(): void
    {
        $wrongPassword = $this->loginAs('camille', 'mauvais');
        $unknownUser = $this->loginAs('inconnu', 'mauvais');

        $this->assertSame(422, $wrongPassword->status);
        $this->assertStringContainsString('Pseudo ou mot de passe incorrect.', $wrongPassword->body);
        $this->assertStringContainsString('Pseudo ou mot de passe incorrect.', $unknownUser->body);
    }

    public function testBlocksLoginAfterTooManyFailures(): void
    {
        for ($i = 0; $i < LoginThrottle::MAX_ATTEMPTS; $i++) {
            $this->loginAs('camille', 'mauvais');
        }

        $blocked = $this->loginAs('camille');
        $this->assertStringContainsString('Trop de tentatives', $blocked->body);

        // Another visitor (another IP) can still log in.
        $this->sessionData = [];
        $other = $this->post('/connexion', ['pseudo' => 'camille', 'password' => 'password'], '10.0.0.2');
        $this->assertRedirect($other, '/carnet');
    }

    public function testKeepsOnlyTheUserIdInTheSession(): void
    {
        $this->loginAs('camille');

        $this->assertSame(1, $this->sessionData['user_id']);
        $this->assertStringNotContainsString('$2y$', serialize($this->sessionData), 'no password hash in the session');
    }

    public function testSendsTheVisitorBackToThePageTheyWanted(): void
    {
        $this->assertRedirect($this->get('/carnet/ajouter?ville=5'), '/connexion');

        $this->assertRedirect($this->loginAs('camille'), '/carnet/ajouter?ville=5');
    }

    public function testRefusesAFormWithoutAValidCsrfToken(): void
    {
        $response = $this->post('/connexion', ['pseudo' => 'camille', 'password' => 'password', '_token' => 'faux']);

        $this->assertSame(419, $response->status);
        $this->assertArrayNotHasKey('user_id', $this->sessionData);
    }
}
