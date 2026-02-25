<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Application\Command\SignUp;

use App\User\Application\Command\SignUp\Request\Command;
use App\User\Application\Command\SignUp\Request\Handler;
use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\Service\Flusher;
use App\User\Domain\Service\PasswordHasher;
use App\User\Domain\ValueObject\Email;
use DomainException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase
{
    public function testSuccess(): void
    {
        $repo = $this->createMock(UserRepository::class);
        $hasher = $this->createMock(PasswordHasher::class);
        $flusher = $this->createMock(Flusher::class);

        $command = new Command(email: 'test@example.com', password: 'secret');

        $repo->expects(self::once())
            ->method('existsByEmail')
            ->with(self::isInstanceOf(Email::class))
            ->willReturn(false);

        $hasher->expects(self::once())
            ->method('hash')
            ->with('secret')
            ->willReturn('HASHED');

        $repo->expects(self::once())
            ->method('add')
            ->with(self::callback(function (User $user) {
                return true;
            }));

        $flusher->expects(self::once())
            ->method('flush');

        $handler = new Handler($repo, $hasher, $flusher);
        $handler->handle($command);
    }

    public function testEmailAlreadyExists(): void
    {
        $repo = $this->createMock(UserRepository::class);
        $hasher = $this->createMock(PasswordHasher::class);
        $flusher = $this->createMock(Flusher::class);

        $command = new Command(email: 'test@example.com', password: 'secret');

        $repo->method('existsByEmail')->willReturn(true);

        $repo->expects(self::never())->method('add');
        $hasher->expects(self::never())->method('hash');
        $flusher->expects(self::never())->method('flush');

        $handler = new Handler($repo, $hasher, $flusher);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Email already exists.');

        $handler->handle($command);
    }

    public function testInvalidEmail(): void
    {
        $repo = $this->createMock(UserRepository::class);
        $hasher = $this->createMock(PasswordHasher::class);
        $flusher = $this->createMock(Flusher::class);

        $command = new Command(email: 'not-an-email', password: 'secret');

        $repo->expects(self::never())->method('existsByEmail');
        $repo->expects(self::never())->method('add');
        $hasher->expects(self::never())->method('hash');
        $flusher->expects(self::never())->method('flush');

        $handler = new Handler($repo, $hasher, $flusher);

        $this->expectException(InvalidArgumentException::class);

        $handler->handle($command);
    }
}
