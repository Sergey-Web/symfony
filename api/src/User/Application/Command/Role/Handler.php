<?php

declare(strict_types=1);

namespace App\User\Application\Command\Role;

use App\User\Domain\Enum\UserRole;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\Service\Flusher;
use App\User\Domain\ValueObject\Id;
use DomainException;

final readonly class Handler
{
    public function __construct(
        private UserRepository $userRepository,
        private Flusher $flusher,
    ) {}

    public function handle(Command $command): void
    {
        $user = $this->userRepository->findByUserId(Id::fromString($command->userId));

        if ($user === null) {
            throw new DomainException('User not found.');
        }

        $user->changeRole(UserRole::fromValue($command->role));

        $this->flusher->flush();

    }
}
