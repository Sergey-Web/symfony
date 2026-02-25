<?php

declare(strict_types=1);

namespace App\User\Domain\ValueObject;

use DomainException;
use Ramsey\Uuid\Uuid;

final readonly class UserId
{
    private(set) string $value;

    private function __construct(string $value)
    {
        if (!Uuid::isValid($value)) {
            throw new DomainException('Invalid UserId.');
        }

        $this->value = mb_strtolower($value);
    }

    public static function next(): self
    {
        return new self(Uuid::uuid7()->toString());
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }
}
