<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Domain\ValueObject;

use App\User\Domain\ValueObject\Name;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class NameTest extends TestCase
{
    public function testSuccessWithLastName(): void
    {
        $name = new Name('John', 'Doe');

        self::assertSame('John', $name->firstName);
        self::assertSame('Doe', $name->lastName);
    }

    public function testSuccessWithoutLastName(): void
    {
        $name = new Name('John', null);
        self::assertSame('John', $name->firstName);
        self::assertNull($name->lastName);
    }

    public function testFirstNameTooShort(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Name('D', 'Doe');
    }

    public function testFirstNameTooLong(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Name(str_repeat('a', 51), 'Doe');
    }

    public function testLastNameTooShort(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Name('John', 'D');
    }

    public function testLastNameTooLong(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Name('John', str_repeat('a', 51));
    }
}
