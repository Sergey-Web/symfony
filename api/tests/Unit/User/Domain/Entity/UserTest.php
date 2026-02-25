<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Domain\Entity;

use App\User\Domain\Entity\User;
use App\User\Domain\Enum\UserStatus;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\ConfirmToken;
use App\User\Domain\ValueObject\UserId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserIsCreated(): void
    {
        $id = UserId::next();
        $email = new Email('test@example.com');
        $hash = 'HASH';
        $date = new DateTimeImmutable();
        $token = ConfirmToken::generate();

        $user = new User($id, $email, $hash, $date, $token);

        self::assertTrue($user->status === UserStatus::Wait);
        self::assertFalse($user->status === UserStatus::Active);

        self::assertSame($id, $user->id);
        self::assertSame($email, $user->email);
        self::assertSame($hash, $user->hash);
        self::assertSame($date, $user->date);
        self::assertSame($token, $user->signUpToken);
    }
}
