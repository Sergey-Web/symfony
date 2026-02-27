<?php

declare(strict_types=1);

namespace App\User\Domain\ValueObject;

use DomainException;

class ProviderUserId
{
    public function __construct(public string $value)
    {
        $v = trim($value);
        if ($v === '') {
            throw new DomainException('Empty provider user id.');
        }
    }
}
