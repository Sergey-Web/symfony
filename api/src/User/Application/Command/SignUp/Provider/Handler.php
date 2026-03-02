<?php

declare(strict_types=1);

namespace App\User\Application\Command\SignUp\Provider;

use App\User\Domain\Entity\User;
use App\User\Domain\Enum\AuthProvider;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\Service\Flusher;
use App\User\Domain\Service\PasswordHasher;
use App\User\Domain\Service\SignUpConfirmationSender;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Id;
use App\User\Domain\ValueObject\Name;
use DateTimeImmutable;
use DomainException;
use InvalidArgumentException;

final class Handler
{
    public function __construct(
        private UserRepository $userRepository,
        private PasswordHasher $passwordHasher,
        private Flusher $flusher,
        private SignUpConfirmationSender $signUpConfirmationSender,
    ) {}

    public function handle(Command $command): void
    {
        if (AuthProvider::tryFrom($command->provider) === null) {
            throw new InvalidArgumentException('Invalid auth provider');
        }

        $provider = AuthProvider::from($command->provider);
        if (
            $this->userRepository->existsByAuthProvider(
                $provider,
                $command->externalId
            )
        ) {
            throw new DomainException('Email already exists.');
        }

        $user = User::signUpByAuth(
            id: Id::next(),
            provider: $provider,
            externalId: $command->externalId,
            name: new Name($command->firstName, $command->lastName),
            createdAt: new DateTimeImmutable(),
        );

        $this->userRepository->add($user);

        $this->flusher->flush();
    }
}
