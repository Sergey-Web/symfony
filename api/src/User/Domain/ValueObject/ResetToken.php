<?php

declare(strict_types=1);

namespace App\User\Domain\ValueObject;

use DateTimeImmutable;

use Webmozart\Assert\Assert;
use Doctrine\ORM\Mapping as ORM;

final readonly class ResetToken
{
    public function __construct(
        #[ORM\Column(name: 'reset_token', type: 'string', unique: true, nullable: true)]
        private(set) string $value,

        #[ORM\Column(name: 'expires_at', type: 'datetime_immutable', nullable: true)]
        private DateTimeImmutable $expiresAt,
    ) {
        Assert::notEmpty($value);
    }

    public function isExpired(DateTimeImmutable $now): bool
    {
        return $this->expiresAt <= $now;
    }
}
