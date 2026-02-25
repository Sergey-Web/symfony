<?php

declare(strict_types=1);

namespace App\User\Domain\Repository;

use App\User\Domain\Entity\User;
use App\User\Domain\ValueObject\Email;

interface UserRepository
{
    public function hasByEmail(Email $email): bool;

    public function findByEmail(string $email): ?User;

    public function add(User $user): void;
}
