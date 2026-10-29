<?php

namespace App\Repository;

use PDO;

/**
 * Blocks logins for a pseudo + IP pair after too many failures, to slow down
 * password guessing. Failures are stored in the database, so clearing cookies
 * does not reset the counter.
 */
final class LoginThrottle
{
    public const MAX_ATTEMPTS = 5;
    public const WINDOW_MINUTES = 15;

    public function __construct(private readonly PDO $pdo)
    {
    }

    public function tooManyAttempts(string $pseudo, string $ip): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM login_attempts WHERE attempt_key = ? AND attempted_at > ?');
        $stmt->execute([$this->key($pseudo, $ip), $this->windowStart()]);

        return (int) $stmt->fetchColumn() >= self::MAX_ATTEMPTS;
    }

    public function hit(string $pseudo, string $ip): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO login_attempts (attempt_key, attempted_at) VALUES (?, ?)');
        $stmt->execute([$this->key($pseudo, $ip), date('Y-m-d H:i:s')]);

        // Housekeeping: old attempts are no longer useful.
        $this->pdo->prepare('DELETE FROM login_attempts WHERE attempted_at < ?')->execute([$this->windowStart()]);
    }

    public function clear(string $pseudo, string $ip): void
    {
        $this->pdo->prepare('DELETE FROM login_attempts WHERE attempt_key = ?')->execute([$this->key($pseudo, $ip)]);
    }

    private function key(string $pseudo, string $ip): string
    {
        return hash('sha256', mb_strtolower($pseudo) . '|' . $ip);
    }

    private function windowStart(): string
    {
        return date('Y-m-d H:i:s', time() - self::WINDOW_MINUTES * 60);
    }
}
