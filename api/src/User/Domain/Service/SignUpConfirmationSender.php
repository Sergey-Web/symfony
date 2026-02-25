<?php

declare(strict_types=1);

namespace App\User\Domain\Service;

use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\ConfirmToken;

interface SignUpConfirmationSender
{
    public function send(Email $email, ConfirmToken $token): void;
}
