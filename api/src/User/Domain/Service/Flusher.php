<?php

declare(strict_types=1);

namespace App\User\Domain\Service;

interface Flusher
{
    public function flush(): void;
}
