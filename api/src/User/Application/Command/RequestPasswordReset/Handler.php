<?php

declare(strict_types=1);

namespace App\User\Application\Command\RequestPasswordReset;

use App\User\Domain\Repository\UserRepository;
use App\User\Domain\Service\Flusher;
use App\User\Domain\Service\RequestResetPasswordSender;
use App\User\Domain\Service\SignUpConfirmationSender;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\ResetToken;
use DateMalformedStringException;
use DateTimeImmutable;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

final readonly class Handler
{
    public function __construct(
        private UserRepository $userRepository,
        private Flusher $flusher,
        private RequestResetPasswordSender $resetPasswordSender,
    ) {
    }

    /**
     * @throws DateMalformedStringException
     */
    public function handler(Command $command): void
    {
        $user = $this->userRepository->findByEmail(new Email($command->email));

        if ($user === null) {
            throw new InvalidArgumentException('User not found.');
        }

        $resetToken = new ResetToken(
            Uuid::uuid4()->toString(),
            new DateTimeImmutable()
        );

        $user->requestPasswordReset($resetToken);

        $this->flusher->flush();

        $this->resetPasswordSender->send($user->email, $user->resetToken);
    }
}
