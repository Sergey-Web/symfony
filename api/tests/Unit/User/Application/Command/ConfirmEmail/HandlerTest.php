<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Application\Command\ConfirmEmail;

use App\Tests\Builder\User\UserBuilder;
use App\User\Application\Command\ConfirmEmail\Command;
use App\User\Application\Command\ConfirmEmail\Handler;
use App\User\Domain\Entity\User;
use App\User\Domain\Enum\UserRole;
use App\User\Domain\Enum\UserStatus;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\Service\Flusher;
use App\User\Domain\ValueObject\ConfirmToken;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Id;
use App\User\Domain\ValueObject\Name;
use App\User\Domain\ValueObject\Password;
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
        $userIdentity = 'f2d49cd8-22e2-4d76-a8f5-6fd7a893f222';
        $userId = Id::fromString($userIdentity);

        $confirmToken = ConfirmToken::fromString($token);
        $user = new UserBuilder(id: $userId)->viaSignUpEmail(confirmToken: $confirmToken)->build();

        $userRepository->expects(self::once())
            ->method('findByUserId')
            ->with($userId)
            ->willReturn($user);

        $flusher->expects(self::once())->method('flush');

        $handler = new Handler($userRepository, $flusher);
        $command = new Command($userIdentity, $token);

        $handler->handle($command);

        self::assertSame(UserStatus::Active, $user->status);
    }

    public function testTokenNotFound(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $flusher = $this->createMock(Flusher::class);
        $token = 'f2d49cd8-72e2-4d76-a8f5-6fd7a893f110';
        $userIdentity = 'f2d49cd8-22e2-4d76-a8f5-6fd7a893f222';

        $userRepository->expects(self::once())
            ->method('findByUserId')
            ->willReturn(null);

        $flusher->expects(self::never())->method('flush');

        $handler = new Handler($userRepository, $flusher);
        $command = new Command($userIdentity, $token);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('User with confirm token "f2d49cd8-72e2-4d76-a8f5-6fd7a893f110" not found');

        $handler->handle($command);
    }

    public function testInvalidConfirmToken(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $flusher = $this->createMock(Flusher::class);
        $token = 'invalid-token';
        $userId = 'f2d49cd8-22e2-4d76-a8f5-6fd7a893f222';

        $userRepository->expects(self::never())->method('findByUserId');
        $flusher->expects(self::never())->method('flush');

        $handler = new Handler($userRepository, $flusher);
        $command = new Command($userId, $token);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid ConfirmToken.');

        $handler->handle($command);
    }

    public function testUserAlreadyConfirmed(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $flusher = $this->createMock(Flusher::class);
        $token = 'f2d49cd8-72e2-4d76-a8f5-6fd7a893f110';
        $userIdentity = 'f2d49cd8-22e2-4d76-a8f5-6fd7a893f222';
        $userId = Id::fromString($userIdentity);

        $confirmToken = ConfirmToken::fromString($token);
        $user = $this->makeUser($confirmToken, $userId);
        $user->confirmSignUp(ConfirmToken::fromString($token));

        $userRepository->expects(self::once())
            ->method('findByUserId')
            ->with($userId)
            ->willReturn($user);

        $flusher->expects(self::never())->method('flush');

        $handler = new Handler($userRepository, $flusher);
        $command = new Command($userIdentity, $token);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('User already confirmed.');

        $handler->handle($command);
    }

    private function makeUser(ConfirmToken $confirmToken, Id $userId): User
    {
        return User::signUpWithEmail(
            id: $userId,
            email: new Email('test@example.com'),
            name: new Name('John', 'Doe'),
            confirmToken: $confirmToken,
            password: new Password('password123'),
            createdAt: new DateTimeImmutable(),
            role: UserRole::User,
        );
    }
}
