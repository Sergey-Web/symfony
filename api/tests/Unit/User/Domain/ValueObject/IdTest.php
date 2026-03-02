<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Domain\ValueObject;

use App\User\Domain\ValueObject\Id;
use DomainException;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class IdTest extends TestCase
{
    public function testFromStringSuccess(): void
    {
        $value = 'f2d49cd8-22e2-4d76-a8f5-6fd7a893f222';
        $id = Id::fromString($value);

        self::assertSame($value, $id->value);
    }

    public function testFromStringNormalizesToLowercase(): void
    {
        $value = 'F2D49CD8-22E2-4D76-A8F5-6FD7A893F222';

        $id = Id::fromString($value);

        self::assertSame(strtolower($value), $id->value);
    }

    public function testFromStringInvalid(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid UserId.');

        Id::fromString('invalid');
    }

    public function testNextGeneratesValidAndUniqueId(): void
    {
        $id1 = Id::next();
        $id2 = Id::next();

        self::assertTrue(Uuid::isValid($id1->value));
        self::assertTrue(Uuid::isValid($id2->value));

        self::assertNotSame($id1->value, $id2->value);
    }
}
