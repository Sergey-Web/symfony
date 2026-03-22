<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Domain\Entity\User;

use App\Tests\Builder\User\UserBuilder;
use App\User\Domain\Enum\UserRole;
use DomainException;
use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
    public function testChangeRole(): void
    {
        $user = new UserBuilder()->viaSignUpEmail()->confirmed()->build();
        $user->changeRole(UserRole::Admin);

        $this->assertSame(UserRole::Admin, $user->role);
    }

    public function testRoleIsAlreadyAssigned(): void
    {
        $user = new UserBuilder()->viaSignUpEmail()->confirmed()->build();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('This role is already assigned.');

        $user->changeRole(UserRole::User);
    }
}
