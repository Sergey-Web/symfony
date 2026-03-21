<?php

declare(strict_types=1);

namespace App\User\Application\Command\SignUp\Provider;

use App\User\Domain\Entity\User;
use App\User\Domain\Enum\ExternalProvider;
use App\User\Domain\Enum\Role;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\Service\Flusher;
use App\User\Domain\ValueObject\Id;
use App\User\Domain\ValueObject\Name;
use DateTimeImmutable;

final readonly class Handler
{
    public function __construct(
        private UserRepository $userRepository,
        private Flusher $flusher,
    ) {}

    public function handle(Command $command): void
    {
        $user = User::signUpWithExternalProvider(
            id: Id::next(),
            provider: ExternalProvider::fromValue($command->provider),
            externalId: $command->externalId,
            name: new Name($command->firstName, $command->lastName),
            createdAt: new DateTimeImmutable(),
            role: Role::User
        );

        $this->userRepository->add($user);

        $this->flusher->flush();
    }
}
