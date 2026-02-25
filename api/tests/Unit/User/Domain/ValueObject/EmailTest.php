<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Domain\ValueObject;

use App\User\Domain\ValueObject\Email;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    public function testValidEmail(): void
    {
        $email = new Email('test@example.com');

        self::assertSame('test@example.com', $email->value);
    }

    public function testInvalidEmail(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Email('test');
    }

    public function testEmailWithSpacesThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Email(' test ');
    }
}
