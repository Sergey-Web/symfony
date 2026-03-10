<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Domain\ValueObject;

use App\User\Domain\ValueObject\ConfirmToken;
use DomainException;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class ConfirmTokenTest extends TestCase
{
    public function testFromStringSuccess(): void
    {
        $value = 'f2d49cd8-72e2-4d76-a8f5-6fd7a893f110';

        $token = ConfirmToken::fromString($value);

        self::assertSame($value, $token->value);
    }

    public function testFromStringInvalid(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid ConfirmToken.');

        ConfirmToken::fromString('invalid-token');
    }

    public function testGenerateReturnsValidUuid(): void
    {
        $token = ConfirmToken::generate();

        self::assertTrue(Uuid::isValid($token->value));
    }

    public function testGenerateReturnsUniqueValues(): void
    {
        $token1 = ConfirmToken::generate();
        $token2 = ConfirmToken::generate();

        self::assertNotSame($token1->value, $token2->value);
    }
}
