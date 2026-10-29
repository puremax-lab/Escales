<?php

namespace Tests\Core;

use App\Core\Validator;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    public function testTrimsValuesAndAcceptsValidData(): void
    {
        $v = (new Validator(['name' => '  Léna  ', 'age' => '30']))
            ->required('name')->length('name', 2, 10)
            ->integer('age', 13, 120, 'Âge invalide.');

        $this->assertFalse($v->fails());
        $this->assertSame('Léna', $v->value('name'));
    }

    public function testKeepsOnlyTheFirstErrorOfAField(): void
    {
        $v = (new Validator(['name' => '']))->required('name')->length('name', 2, 10);

        $this->assertSame(['name' => 'Ce champ est obligatoire.'], $v->errors());
    }

    public function testCountsCharactersNotBytes(): void
    {
        $v = (new Validator(['name' => 'éé']))->length('name', 2, 2);

        $this->assertFalse($v->fails());
    }

    public function testRejectsImpossibleOrOutOfRangeDates(): void
    {
        $rule = fn (string $date) => (new Validator(['d' => $date]))
            ->date('d', '2000-01-01', '2026-12-31', 'Date invalide.')
            ->fails();

        $this->assertFalse($rule('2024-02-29'));
        $this->assertTrue($rule('2023-02-29'), 'not a leap year');
        $this->assertTrue($rule('2027-01-01'), 'after the maximum');
        $this->assertTrue($rule('01/02/2024'), 'wrong format');
    }

    public function testComparesTwoFields(): void
    {
        $v = (new Validator(['password' => 'secret123', 'confirm' => 'secret124']))
            ->same('confirm', 'password', 'Différents.');

        $this->assertSame(['confirm' => 'Différents.'], $v->errors());
    }

    public function testIgnoresArraysSentInsteadOfText(): void
    {
        $v = (new Validator(['name' => ['injected']]))->required('name');

        $this->assertTrue($v->fails());
    }
}
