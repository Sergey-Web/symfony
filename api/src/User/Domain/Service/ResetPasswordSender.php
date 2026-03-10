<?php

declare(strict_types=1);

namespace App\User\Domain\Service;

use App\User\Domain\ValueObject\ConfirmToken;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\ResetToken;

interface ResetPasswordSender
{
    public function send(Email $email, ResetToken $token): void;
}
