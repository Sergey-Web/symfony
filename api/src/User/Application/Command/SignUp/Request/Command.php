<?php

declare(strict_types=1);

namespace App\User\Application\Command\SignUp\Request;

final readonly class Command
{
    public string $email;

    public string $password;
}
