<?php

declare(strict_types=1);

namespace App\User\Infrastructure;

use App\User\Domain\Service\Flusher;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineFlusher implements Flusher
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
