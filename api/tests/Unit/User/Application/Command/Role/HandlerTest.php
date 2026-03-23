<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Application\Command\Role;

use App\Tests\Builder\User\UserBuilder;
use App\User\Application\Command\Role\Command;
use App\User\Application\Command\Role\Handler;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\Service\Flusher;
use App\User\Domain\ValueObject\Id;
use DomainException;
use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase
{
    public function testSuccess(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $flusher = $this->createMock(Flusher::class);
        $user = new UserBuilder()->viaSignUpExternalProvider()->build();

        $userRepository
            ->expects(self::once())
            ->method('findByUserId')
            ->with(Id::fromString($user->id))
            ->willReturn($user);

        $flusher->expects(self::once())->method('flush');
        $command = new Command($user->id, 'ROLE_ADMIN');

        new Handler($userRepository, $flusher)->handle($command);
    }

    public function testUserNotFound(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $flusher = $this->createMock(Flusher::class);
        $userId = Id::next();

        $userRepository
            ->expects(self::once())
            ->method('findByUserId')
            ->with($userId)
            ->willReturn(null);

        $flusher->expects(self::never())->method('flush');
        $command = new Command($userId->value, 'ROLE_ADMIN');

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('User not found.');

        new Handler($userRepository, $flusher)->handle($command);
    }
}
