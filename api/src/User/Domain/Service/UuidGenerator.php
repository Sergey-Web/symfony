<?php

declare(strict_types=1);

namespace App\User\Domain\Service;

interface UuidGenerator
{
    public function generate(): string;
}
