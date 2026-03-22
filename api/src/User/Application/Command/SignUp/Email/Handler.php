<?php

declare(strict_types=1);

namespace App\User\Application\Command\SignUp\Email;

use App\User\Domain\Entity\User;
use App\User\Domain\Enum\UserRole;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\Service\Flusher;
use App\User\Domain\Service\SignUpConfirmationSender;
use App\User\Domain\ValueObject\ConfirmToken;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Id;
use App\User\Domain\ValueObject\Name;
use App\User\Domain\ValueObject\Password;
use DateTimeImmutable;
use DomainException;

final readonly class Handler
{
    public function __construct(
        private UserRepository $userRepository,
        private Flusher $flusher,
        private SignUpConfirmationSender $signUpConfirmationSender,
    ) {}

    public function handle(Command $command): void
    {
        $email = new Email($command->email);

        if ($this->userRepository->existsByEmail($email)) {
            throw new DomainException('Email already exists.');
        }

        $user = User::signUpWithEmail(
            id: Id::next(),
            email: $email,
            name: new Name($command->firstName, $command->lastName),
            confirmToken: ConfirmToken::generate(),
            password: new Password($command->password),
            createdAt: new DateTimeImmutable(),
            role: UserRole::User
        );

        $this->userRepository->add($user);

        $this->flusher->flush();

        $this->signUpConfirmationSender->send(
            $email,
            $user->confirmToken,
        );
    }
}
