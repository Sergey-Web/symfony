<?php

declare(strict_types=1);

namespace App\User\Domain\ValueObject;

use DateMalformedStringException;
use DateTimeImmutable;

use Webmozart\Assert\Assert;
use Doctrine\ORM\Mapping as ORM;

final readonly class ResetToken
{
    private const int TTL = 3600;
    private const int COOLDOWN = 300;

    #[ORM\Column(name: 'expires_at', type: 'datetime_immutable', nullable: true)]
    private DateTimeImmutable $expiresAt;

    /**
     * @throws DateMalformedStringException
     */public function __construct(
        #[ORM\Column(name: 'reset_token', type: 'string', unique: true, nullable: true)]
        private(set) string $value,
        DateTimeImmutable $expiresAt,
    ) {
        Assert::notEmpty($value);

        $this->expiresAt = $expiresAt->modify('+' . self::TTL . ' seconds');
    }

    public function isExpired(DateTimeImmutable $now): bool
    {
        return $this->expiresAt <= $now;
    }

    /**
     * @throws DateMalformedStringException
     */
    public function isTooEarly(DateTimeImmutable $now): bool
    {
        return $now < $this->createdAt()->modify('+' . self::COOLDOWN . ' seconds');
    }

    /**
     * @throws DateMalformedStringException
     */
    private function createdAt(): DateTimeImmutable
    {
        return $this->expiresAt->modify('-' . self::TTL . ' seconds');
    }
}
