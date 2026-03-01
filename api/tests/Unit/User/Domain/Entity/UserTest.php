<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Domain\Entity;

use App\User\Domain\Entity\User;
use App\User\Domain\Enum\UserStatus;
use App\User\Domain\ValueObject\Email;
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
        $hash = new NativePasswordHasher()->hash($password);
        $date = new DateTimeImmutable();

        $user = User::signUpByEmail($email, $name, $hash, $date);

        self::assertTrue($user->status === UserStatus::Wait);
        self::assertFalse($user->status === UserStatus::Active);

        self::assertFalse(new NativePasswordHasher()->verify($password . ' ', $hash));
        self::assertTrue(new NativePasswordHasher()->verify($password, $hash));
        self::assertNotSame(new NativePasswordHasher()->hash($password), $hash);

        self::assertSame($email, $user->email);
        self::assertSame($hash, $user->hash);
        self::assertSame($date, $user->createdAt);
    }
}
