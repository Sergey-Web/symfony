<?php

declare(strict_types=1);

namespace App\User\Infrastructure;

use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\UserId;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineUserRepository implements UserRepository
{
    public function __construct(private EntityManagerInterface $em) {}

    public function add(User $user): void
    {
        $this->em->persist($user);
    }

    public function findByEmail(Email $email): ?User
    {
        return $this->em->getRepository(User::class)->findOneBy([
            'email' => $email->value,
        ]);
    }

    public function existsByEmail(Email $email): bool
    {
        return $this->findByEmail($email) !== null;
    }

    public function findByUserId(UserId $userId): ?User
    {
        return $this->em->getRepository(User::class)->find($userId->value);
    }
}
