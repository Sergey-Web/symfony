<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Application\Command\ConfirmEmail;

use App\User\Application\Command\ConfirmEmail\Command;
use App\User\Application\Command\ConfirmEmail\Handler;
use App\User\Domain\Entity\User;
use App\User\Domain\Enum\UserStatus;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\Service\Flusher;
use App\User\Domain\ValueObject\ConfirmToken;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Id;
use App\User\Domain\ValueObject\Name;
use DateTimeImmutable;
use DomainException;
use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase
{
    public function testSuccess(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $flusher = $this->createMock(Flusher::class);
        $token = 'f2d49cd8-72e2-4d76-a8f5-6fd7a893f110';

        $user = $this->makeUser();
        $confirmToken = ConfirmToken::fromString($token);

        $userRepository->expects(self::once())
            ->method('findByConfirmToken')
            ->with($confirmToken)
            ->willReturn($user);

        $flusher->expects(self::once())->method('flush');

        $handler = new Handler($userRepository, $flusher);
        $command = new Command($token);

        $handler->handle($command);

        self::assertSame(UserStatus::Active, $user->status);
    }

    public function testTokenNotFound(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $flusher = $this->createMock(Flusher::class);
        $token = 'f2d49cd8-72e2-4d76-a8f5-6fd7a893f110';

        $userRepository->expects(self::once())
            ->method('findByConfirmToken')
            ->willReturn(null);

        $flusher->expects(self::never())->method('flush');

        $handler = new Handler($userRepository, $flusher);
        $command = new Command($token);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('User with confirm token "f2d49cd8-72e2-4d76-a8f5-6fd7a893f110" not found');

        $handler->handle($command);
    }

    public function testInvalidConfirmToken(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $flusher = $this->createMock(Flusher::class);
        $token = 'invalid-token';

        $userRepository->expects(self::never())->method('findByConfirmToken');
        $flusher->expects(self::never())->method('flush');

        $handler = new Handler($userRepository, $flusher);
        $command = new Command($token);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid ConfirmToken.');

        $handler->handle($command);
    }

    public function testUserAlreadyConfirmed(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $flusher = $this->createMock(Flusher::class);
        $token = 'f2d49cd8-72e2-4d76-a8f5-6fd7a893f110';

        $user = $this->makeUser();
        $user->confirmSignUp();;

        $confirmToken = ConfirmToken::fromString($token);

        $userRepository->expects(self::once())
            ->method('findByConfirmToken')
            ->with($confirmToken)
            ->willReturn($user);

        $flusher->expects(self::never())->method('flush');

        $handler = new Handler($userRepository, $flusher);
        $command = new Command($token);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('User already confirmed.');

        $handler->handle($command);
    }

    private function makeUser(): User
    {
        return User::signUpByEmail(
            email: new Email('test@example.com'),
            name: new Name('John', 'Doe'),
            hash: 'HASHED',
            createdAt: new DateTimeImmutable(),
        );
    }
}
