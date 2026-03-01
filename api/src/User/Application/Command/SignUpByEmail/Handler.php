<?php

declare(strict_types=1);

namespace App\User\Application\Command\SignUpByEmail;

use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\Service\Flusher;
use App\User\Domain\Service\PasswordHasher;
use App\User\Domain\Service\SignUpConfirmationSender;
use App\User\Domain\ValueObject\ConfirmToken;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Id;
use App\User\Domain\ValueObject\Name;
use DateTimeImmutable;
use DomainException;

final readonly class Handler
{
    public function __construct(
        private UserRepository $userRepository,
        private PasswordHasher $passwordHasher,
        private Flusher $flusher,
        private SignUpConfirmationSender $signUpConfirmationSender,
    ) {}

    public function handle(Command $command): void
    {
        $email = new Email($command->email);

        if ($this->userRepository->existsByEmail($email)) {
            throw new DomainException('Email already exists.');
        }

        $user = User::signUpByEmail(
            email: $email,
            name: new Name($command->firstName, $command->lastName),
            hash: $this->passwordHasher->hash($command->password),
            createdAt: new DateTimeImmutable(),
        );

        $this->userRepository->add($user);

        $this->flusher->flush();

        $this->signUpConfirmationSender->send(
            $email,
            $user->confirmToken,
        );
    }
}
