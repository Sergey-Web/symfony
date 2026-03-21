<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Domain\Entity\User;

use App\Tests\Builder\User\UserBuilder;
use App\User\Domain\Enum\Role;
use DomainException;
use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
    public function testChangeRole(): void
    {
        $user = new UserBuilder()->viaSignUpEmail()->confirmed()->build();
        $user->changeRole(Role::Admin);

        $this->assertSame(Role::Admin, $user->role);
    }

    public function testRoleIsAlreadyAssigned(): void
    {
        $user = new UserBuilder()->viaSignUpEmail()->confirmed()->build();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('This role is already assigned.');

        $user->changeRole(Role::User);
    }
}
