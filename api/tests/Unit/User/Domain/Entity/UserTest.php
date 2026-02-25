<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Domain\Entity;

use App\User\Domain\Entity\User;
use App\User\Domain\ValueObject\Email;
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

        $user = new User($id, $email, $hash, $date);

        self::assertSame($id, $user->id);
        self::assertSame($email, $user->email);
        self::assertSame($hash, $user->hash);
        self::assertSame($date, $user->date);
    }
}
