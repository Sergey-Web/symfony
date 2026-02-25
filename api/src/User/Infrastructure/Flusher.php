<?php

declare(strict_types=1);

namespace App\User\Infrastructure;

use Doctrine\ORM\EntityManagerInterface;

final readonly class Flusher
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function flash(): void
    {
        $this->entityManager->flush();
    }
}
