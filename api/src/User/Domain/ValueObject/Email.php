<?php

declare(strict_types=1);

namespace App\User\Domain\ValueObject;

use InvalidArgumentException;

final readonly class Email
{
    public string $email;

    public function __construct(string $value)
    {
        $value = trim($value);

        if ($value === '') {
            throw new InvalidArgumentException('Email cannot be empty');
        }

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email');
        }

        $this->email = mb_strtolower(trim($value));
    }
}
