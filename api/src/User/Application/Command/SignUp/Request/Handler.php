<?php

declare(strict_types=1);

namespace App\User\Application\Command\SignUp\Request;

use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\ValueObject\Email;
use App\User\Infrastructure\Flusher;

final readonly class Handler
{
    public function __construct(
        private UserRepository $userRepository,
        private PasswordHasher $passwordHasher,
        private Flusher $flusher
    ) {
    }

    public function handle(Command $command): void
    {
        $email = new Email($command->email);

        if ($this->userRepository->hasByEmail($email)) {
            throw new \DomainException('Email already exists.');
        }

        $user = new User(

        );

    }
}
