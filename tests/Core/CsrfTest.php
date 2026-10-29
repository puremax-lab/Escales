<?php

namespace Tests\Core;

use App\Core\Csrf;
use App\Core\Session;
use PHPUnit\Framework\TestCase;

final class CsrfTest extends TestCase
{
    public function testKeepsTheSameTokenForTheWholeSession(): void
    {
        $data = [];
        $csrf = new Csrf(new Session($data));

        $token = $csrf->token();

        $this->assertSame(64, strlen($token));
        $this->assertSame($token, $csrf->token());
        $this->assertTrue($csrf->isValid($token));
    }

    public function testRejectsAMissingOrWrongToken(): void
    {
        $data = [];
        $csrf = new Csrf(new Session($data));
        $csrf->token();

        $this->assertFalse($csrf->isValid(null));
        $this->assertFalse($csrf->isValid('wrong'));
        $this->assertFalse($csrf->isValid(['array']));
    }

    public function testRejectsEverythingBeforeATokenExists(): void
    {
        $data = [];

        $this->assertFalse((new Csrf(new Session($data)))->isValid(''));
    }
}
