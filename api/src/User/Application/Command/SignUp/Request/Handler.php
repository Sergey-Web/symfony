<?php

declare(strict_types=1);

namespace App\User\Application\Command\SignUp\Request;

use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\Service\Flusher;
use App\User\Domain\Service\PasswordHasher;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\UserId;
use DateTimeImmutable;
use DomainException;

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

        if ($this->userRepository->existsByEmail($email)) {
            throw new DomainException('Email already exists.');
        }

        $user = new User(
            id: UserId::next(),
            email: $email,
            hash: $this->passwordHasher->hash($command->password),
            date: new DateTimeImmutable(),
        );

        $this->userRepository->add($user);

        $this->flusher->flush();

    }
}
