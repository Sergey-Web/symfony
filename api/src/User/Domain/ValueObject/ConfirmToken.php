<?php

declare(strict_types=1);

namespace App\User\Domain\ValueObject;

use DomainException;
use Ramsey\Uuid\Uuid;

final readonly class ConfirmToken
{
    private(set) string $value;

    private function __construct(string $token)
    {
        if (!Uuid::isValid($token)) {
            throw new DomainException('Invalid ConfirmToken.');
        }

        $this->value = $token;
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid4()->toString());
    }

    public static function fromString(string $token): self
    {
        return new self($token);
    }
}
