<?php

declare(strict_types=1);

namespace App\User\Domain\ValueObject;

use Webmozart\Assert\Assert;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final readonly class Email
{
    public function __construct(
        #[ORM\Column(type: 'email', unique: true, nullable: true)]
        private(set) string $value
    ) {
        Assert::email($value);
    }
}
