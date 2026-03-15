<?php

declare(strict_types=1);

namespace App\User\Application\Command\ResetPassword;

use App\User\Domain\Repository\UserRepository;
use App\User\Domain\Service\Flusher;
use App\User\Domain\Service\ResetPasswordSender;
use App\User\Domain\ValueObject\Id;
use App\User\Domain\ValueObject\Password;
use DateTimeImmutable;
use DomainException;

final readonly class Handler
{
    public function __construct(
        private UserRepository $userRepository,
        private Flusher $flusher,
        private ResetPasswordSender $signUpConfirmationSender,
    ) {}

    public function handle(Command $command): void
    {
        $user = $this->userRepository->findByUserId(Id::fromString($command->userId));

        if ($user === null) {
            throw new DomainException('User not found.');
        }

        $user->resetPassword(
            new Password($command->password),
            new DateTimeImmutable()
        );

        $this->flusher->flush();

        $this->signUpConfirmationSender->send($user->email);
    }
}
