<?php

namespace App\Core;

use App\Repository\UserRepository;

/** Who is logged in. Only the user id is kept in the session. */
final class Auth
{
    private const KEY = 'user_id';
    private ?array $user = null;

    public function __construct(private readonly Session $session, private readonly UserRepository $users)
    {
    }

    public function user(): ?array
    {
        $id = $this->session->get(self::KEY);
        if ($this->user === null && is_int($id)) {
            $this->user = $this->users->find($id);
            if ($this->user === null) {
                // The account no longer exists.
                $this->session->remove(self::KEY);
            }
        }

        return $this->user;
    }

    public function id(): ?int
    {
        return $this->user()['id'] ?? null;
    }

    public function login(int $userId): void
    {
        $this->session->regenerate();
        $this->session->set(self::KEY, $userId);
        $this->user = null;
    }

    public function logout(): void
    {
        $this->session->clear();
        $this->user = null;
    }
}
