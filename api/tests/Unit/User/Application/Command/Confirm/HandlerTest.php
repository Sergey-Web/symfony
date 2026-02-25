<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Application\Command\Confirm;

use App\User\Application\Command\Confirm\Command;
use App\User\Application\Command\Confirm\Handler;
use App\User\Domain\Entity\User;
use App\User\Domain\Enum\UserStatus;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\Service\Flusher;
use App\User\Domain\ValueObject\ConfirmToken;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\UserId;
use DateTimeImmutable;
use DomainException;
use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase
{
    public function testSuccess(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $flusher = $this->createMock(Flusher::class);

        $command = new Command(confirmToken: 'f2d49cd8-72e2-4d76-a8f5-6fd7a893f110');
        $user = $this->makeUser(UserStatus::Wait);

        $userRepository->expects(self::once())
            ->method('findByConfirmToken')
            ->with(self::callback(static function (ConfirmToken $confirmToken) use ($command): bool {
                return $confirmToken->value === $command->confirmToken;
            }))
            ->willReturn($user);

        $flusher->expects(self::once())->method('flush');

        $handler = new Handler($userRepository, $flusher);
        $handler->handle($command);

        self::assertSame(UserStatus::Active, $user->status);
    }

    public function testUserNotFound(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $flusher = $this->createMock(Flusher::class);

        $command = new Command(confirmToken: 'f2d49cd8-72e2-4d76-a8f5-6fd7a893f110');

        $userRepository->expects(self::once())
            ->method('findByConfirmToken')
            ->willReturn(null);

        $flusher->expects(self::never())->method('flush');

        $handler = new Handler($userRepository, $flusher);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('User with confirm token "f2d49cd8-72e2-4d76-a8f5-6fd7a893f110" not found');

        $handler->handle($command);
    }

    public function testInvalidConfirmToken(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $flusher = $this->createMock(Flusher::class);

        $command = new Command(confirmToken: 'invalid-token');

        $userRepository->expects(self::never())->method('findByConfirmToken');
        $flusher->expects(self::never())->method('flush');

        $handler = new Handler($userRepository, $flusher);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid ConfirmToken.');

        $handler->handle($command);
    }

    public function testUserAlreadyConfirmed(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $flusher = $this->createMock(Flusher::class);

        $command = new Command(confirmToken: 'f2d49cd8-72e2-4d76-a8f5-6fd7a893f110');
        $user = $this->makeUser(UserStatus::Active);

        $userRepository->expects(self::once())
            ->method('findByConfirmToken')
            ->willReturn($user);

        $flusher->expects(self::never())->method('flush');

        $handler = new Handler($userRepository, $flusher);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('User already confirmed.');

        $handler->handle($command);
    }

    private function makeUser(UserStatus $status): User
    {
        return new User(
            UserId::next(),
            new Email('test@example.com'),
            'HASHED',
            new DateTimeImmutable(),
            ConfirmToken::generate(),
            $status,
        );
    }
}
