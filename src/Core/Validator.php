<?php

namespace App\Core;

/**
 * Server-side validation of submitted form data. Each rule records the first
 * error of a field; later rules on the same field are skipped.
 *
 *     $v = (new Validator($_POST))->required('pseudo')->length('pseudo', 3, 30);
 *     if ($v->fails()) { ... $v->errors() ... }
 */
final class Validator
{
    private array $errors = [];

    public function __construct(private readonly array $data)
    {
    }

    public function value(string $field): string
    {
        $value = $this->data[$field] ?? '';

        return is_string($value) ? trim($value) : '';
    }

    public function required(string $field, string $message = 'Ce champ est obligatoire.'): self
    {
        return $this->check($field, $this->value($field) !== '', $message);
    }

    public function length(string $field, int $min, int $max): self
    {
        $length = mb_strlen($this->value($field));

        return $this->check($field, $length >= $min && $length <= $max, "Entre $min et $max caractères.");
    }

    public function maxLength(string $field, int $max): self
    {
        return $this->check($field, mb_strlen($this->value($field)) <= $max, "$max caractères maximum.");
    }

    public function pattern(string $field, string $regex, string $message): self
    {
        return $this->check($field, preg_match($regex, $this->value($field)) === 1, $message);
    }

    public function integer(string $field, int $min, int $max, string $message): self
    {
        $value = filter_var($this->value($field), FILTER_VALIDATE_INT);

        return $this->check($field, $value !== false && $value >= $min && $value <= $max, $message);
    }

    /** A real calendar date (YYYY-MM-DD) between two dates, both included. */
    public function date(string $field, string $min, string $max, string $message): self
    {
        $value = $this->value($field);
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $valid = $date !== false && $date->format('Y-m-d') === $value && $value >= $min && $value <= $max;

        return $this->check($field, $valid, $message);
    }

    public function same(string $field, string $other, string $message): self
    {
        return $this->check($field, $this->value($field) === $this->value($other), $message);
    }

    /** Custom rule, for checks that need the database (pseudo already taken…). */
    public function rule(string $field, bool $passes, string $message): self
    {
        return $this->check($field, $passes, $message);
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    /** @return array<string, string> field => message */
    public function errors(): array
    {
        return $this->errors;
    }

    private function check(string $field, bool $passes, string $message): self
    {
        if (!$passes && !isset($this->errors[$field])) {
            $this->errors[$field] = $message;
        }

        return $this;
    }
}
