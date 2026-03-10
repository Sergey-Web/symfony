<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Application\Command\SignUp\Email;

use App\User\Application\Command\SignUp\Email\Command;
use App\User\Application\Command\SignUp\Email\Handler;
use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\Service\Flusher;
use App\User\Domain\Service\SignUpConfirmationSender;
use App\User\Domain\ValueObject\ConfirmToken;
use App\User\Domain\ValueObject\Email;
use App\User\Infrastructure\Security\NativePasswordHasher;
use DomainException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase
{
    public function testSuccess(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $flusher = $this->createMock(Flusher::class);
        $signUpConfirmationSender = $this->createMock(SignUpConfirmationSender::class);
        $command = new Command(firstName: 'John', lastName: 'Doe', email: 'test@example.com', password: 'secret12345');

        $userRepository->expects(self::once())
            ->method('existsByEmail')
            ->with(self::isInstanceOf(Email::class))
            ->willReturn(false);

        $userRepository->expects(self::once())
            ->method('add')->with(self::isInstanceOf(User::class));

        $flusher->expects(self::once())
            ->method('flush');

        $signUpConfirmationSender->expects(self::once())
            ->method('send')
            ->with(
                self::isInstanceOf(Email::class),
                self::isInstanceOf(ConfirmToken::class)
            );

        $handler = new Handler($userRepository, $flusher, $signUpConfirmationSender);
        $handler->handle($command);
    }

    public function testEmailAlreadyExists(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $flusher = $this->createMock(Flusher::class);
        $signUpConfirmationSender = $this->createMock(SignUpConfirmationSender::class);
        $command = new Command(firstName: 'John', lastName: 'Doe', email: 'test@example.com', password: 'secret12345');

        $userRepository->expects(self::once())
            ->method('existsByEmail')
            ->with(self::isInstanceOf(Email::class))
            ->willReturn(true);

        $userRepository->expects(self::never())->method('add')->with(self::isInstanceOf(User::class));

        $flusher->expects(self::never())->method('flush');

        $signUpConfirmationSender->expects(self::never())
            ->method('send')
            ->with(
                self::isInstanceOf(Email::class),
                self::isInstanceOf(ConfirmToken::class)
            );

        $handler = new Handler($userRepository, $flusher, $signUpConfirmationSender);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Email already exists.');

        $handler->handle($command);
    }

    public function testInvalidEmail(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $flusher = $this->createMock(Flusher::class);
        $signUpConfirmationSender = $this->createMock(SignUpConfirmationSender::class);
        $command = new Command(firstName: 'John', lastName: 'Doe', email: 'test@example', password: 'secret12345');

        $userRepository->expects(self::never())
            ->method('existsByEmail')
            ->with(self::isInstanceOf(Email::class))
            ->willReturn(false);

        $userRepository->expects(self::never())->method('add')->with(self::isInstanceOf(User::class));
        $flusher->expects(self::never())->method('flush');

        $signUpConfirmationSender->expects(self::never())
            ->method('send')
            ->with(
                self::isInstanceOf(Email::class),
                self::isInstanceOf(ConfirmToken::class)
            );

        $handler = new Handler($userRepository, $flusher, $signUpConfirmationSender);

        $this->expectException(InvalidArgumentException::class);

        $handler->handle($command);
    }
}
