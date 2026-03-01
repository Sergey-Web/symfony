<?php

declare(strict_types=1);

namespace App\User\Domain\ValueObject;

use DomainException;

final readonly class Name
{
    public string $firstName;

    public string $lastName;

    public function __construct(
        string $firstName,
        string $lastName
    ) {
        $this->firstName = $this->guard($firstName);
        $this->lastName = $this->guard($lastName);
    }

    private function guard(string $value): string
    {
        $value = trim($value);

        if (!preg_match('/^[A-Za-z]{2,20}$/', $value)) {
            throw new DomainException(
                'Name must contain only latin letters and be 2-20 characters long.'
            );
        }

        return $value;
    }
}
