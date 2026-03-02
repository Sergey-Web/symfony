<?php

declare(strict_types=1);

namespace App\User\Domain\ValueObject;

use DomainException;
use Ramsey\Uuid\Uuid;
use Doctrine\ORM\Mapping as ORM;

final readonly class ConfirmToken
{
    #[ORM\Column(name: 'confirm_token', type: 'string', unique: true)]
    public string $value;

    private function __construct(string $value)
    {
        if (!Uuid::isValid($value)) {
            throw new DomainException('Invalid ConfirmToken.');
        }

        $this->value = $value;
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
