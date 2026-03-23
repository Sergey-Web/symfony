<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Repository;

use App\User\Domain\Entity\User;
use App\User\Domain\Entity\UserAuthAccount;
use App\User\Domain\Enum\ExternalProvider;
use App\User\Domain\Repository\UserRepository as UserRepositoryInterface;
use App\User\Domain\ValueObject\ConfirmToken;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Id;
use Doctrine\ORM\EntityManagerInterface;

final readonly class UserRepository implements UserRepositoryInterface
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

    public function findByUserId(Id $userId): ?User
    {
        return $this->em->getRepository(User::class)->find($userId->value);
    }

    public function findByConfirmToken(ConfirmToken $confirmToken): ?User
    {
        return $this->em->getRepository(User::class)->findOneBy([
            'confirm_token' => $confirmToken->value,
        ]);
    }

    public function existsByAuthProvider(ExternalProvider $provider, string $externalId): bool
    {
        return $this->em->getRepository(UserAuthAccount::class)->findOneBy([
            'provider' => $provider->value,
            'external_id' => $externalId,
        ]) !== null;
    }
}
