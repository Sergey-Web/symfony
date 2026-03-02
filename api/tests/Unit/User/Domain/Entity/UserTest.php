<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Domain\Entity;

use App\User\Domain\Entity\User;
use App\User\Domain\Enum\UserStatus;
use App\User\Domain\ValueObject\ConfirmToken;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Id;
use App\User\Domain\ValueObject\Name;
use App\User\Infrastructure\Security\NativePasswordHasher;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserIsCreated(): void
    {
        $email = new Email('test@example.com');
        $name = new Name('John', 'Doe');
        $password = 'password';
        $confirmToken = ConfirmToken::generate();
        $hash = new NativePasswordHasher()->hash($password);
        $createdAt = new DateTimeImmutable();

        $user = User::signUpByEmail(
            id: Id::next(),
            email: $email,
            name: $name,
            confirmToken: $confirmToken,
            hash: $hash,
            createdAt: $createdAt,
        );

        self::assertTrue($user->status === UserStatus::Wait);
        self::assertFalse($user->status === UserStatus::Active);

        self::assertFalse(new NativePasswordHasher()->verify($password . ' ', $hash));
        self::assertTrue(new NativePasswordHasher()->verify($password, $hash));
        self::assertNotSame(new NativePasswordHasher()->hash($password), $hash);

        self::assertSame($email, $user->email);
        self::assertSame($hash, $user->passwordHash);
        self::assertSame($createdAt, $user->createdAt);
    }
}
