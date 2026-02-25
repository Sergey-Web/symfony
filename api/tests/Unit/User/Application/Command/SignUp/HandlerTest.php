<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Application\Command\SignUp;

use App\User\Application\Command\SignUp\Request\Command;
use App\User\Application\Command\SignUp\Request\Handler;
use App\User\Domain\Entity\User;
use App\User\Domain\Enum\UserStatus;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\Service\Flusher;
use App\User\Domain\Service\PasswordHasher;
use App\User\Domain\Service\SignUpConfirmationSender;
use App\User\Domain\ValueObject\ConfirmToken;
use App\User\Domain\ValueObject\Email;
use DomainException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase
{
    public function testSuccess(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $hasher = $this->createMock(PasswordHasher::class);
        $flusher = $this->createMock(Flusher::class);
        $signUpConfirmationSender = $this->createMock(SignUpConfirmationSender::class);
        $addedUser = null;

        $command = new Command(email: 'test@example.com', password: 'secret');

        $userRepository->expects(self::once())
            ->method('existsByEmail')
            ->with(self::isInstanceOf(Email::class))
            ->willReturn(false);

        $hasher->expects(self::once())
            ->method('hash')
            ->with('secret')
            ->willReturn('HASHED');

        $userRepository->expects(self::once())
            ->method('add')
            ->with(self::callback(function (User $user) use (&$addedUser) {
                $addedUser = $user;

                self::assertSame(UserStatus::Wait, $user->status);
                self::assertSame('test@example.com', $user->email->value);
                self::assertSame('HASHED', $user->hash);

                return true;
            }));

        $flusher->expects(self::once())
            ->method('flush');

        $signUpConfirmationSender->expects(self::once())
            ->method('send')
            ->with(
                self::callback(static function (Email $email): bool {
                    return $email->value === 'test@example.com';
                }),
                self::callback(static function (ConfirmToken $token) use (&$addedUser): bool {
                    return $addedUser instanceof User && $addedUser->signUpToken->value === $token->value;
                }),
            );


        $handler = new Handler($userRepository, $hasher, $flusher, $signUpConfirmationSender);
        $handler->handle($command);
    }

    public function testEmailAlreadyExists(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $hasher = $this->createMock(PasswordHasher::class);
        $flusher = $this->createMock(Flusher::class);
        $signUpConfirmationSender = $this->createMock(SignUpConfirmationSender::class);

        $command = new Command(email: 'test@example.com', password: 'secret');

        $userRepository->method('existsByEmail')->willReturn(true);

        $userRepository->expects(self::never())->method('add');
        $hasher->expects(self::never())->method('hash');
        $flusher->expects(self::never())->method('flush');
        $signUpConfirmationSender->expects(self::never())->method('send');

        $handler = new Handler($userRepository, $hasher, $flusher, $signUpConfirmationSender);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Email already exists.');

        $handler->handle($command);
    }

    public function testInvalidEmail(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $hasher = $this->createMock(PasswordHasher::class);
        $flusher = $this->createMock(Flusher::class);
        $signUpConfirmationSender = $this->createMock(SignUpConfirmationSender::class);

        $command = new Command(email: 'not-an-email', password: 'secret');

        $userRepository->expects(self::never())->method('existsByEmail');
        $userRepository->expects(self::never())->method('add');
        $hasher->expects(self::never())->method('hash');
        $flusher->expects(self::never())->method('flush');
        $signUpConfirmationSender->expects(self::never())->method('send');

        $handler = new Handler($userRepository, $hasher, $flusher, $signUpConfirmationSender);

        $this->expectException(InvalidArgumentException::class);

        $handler->handle($command);
    }
}
