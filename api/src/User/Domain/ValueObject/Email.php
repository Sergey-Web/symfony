<?php

declare(strict_types=1);

namespace App\User\Domain\ValueObject;

use Webmozart\Assert\Assert;

final readonly class Email
{
    public function __construct(private(set) string $value)
    {
        Assert::email($value);
    }
}
