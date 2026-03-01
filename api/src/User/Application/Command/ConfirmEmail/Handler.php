<?php

declare(strict_types=1);

namespace App\User\Application\Command\ConfirmEmail;

use App\User\Domain\Repository\UserRepository;
use App\User\Domain\Service\Flusher;
use App\User\Domain\ValueObject\ConfirmToken;
use DomainException;

final readonly class Handler
{
    public function __construct(
        private UserRepository $userRepository,
        private Flusher $flusher,
    ) {
    }

    public function handle(Command $command): void
    {
        $confirmToken = ConfirmToken::fromString($command->confirmToken);

        $user = $this->userRepository->findByConfirmToken($confirmToken);

        if ($user === null) {
            throw new DomainException(sprintf('User with confirm token "%s" not found', $command->confirmToken));
        }

        $user->confirmSignUp();

        $this->flusher->flush();
    }
}
