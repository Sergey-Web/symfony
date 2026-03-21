<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Domain\Entity;

use App\Tests\Builder\User\UserBuilder;
use App\User\Domain\Enum\ExternalProvider;
use App\User\Domain\Enum\UserStatus;
use App\User\Domain\ValueObject\ConfirmToken;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Id;
use App\User\Domain\ValueObject\Name;
use App\User\Domain\ValueObject\Password;
use DateTimeImmutable;
use DomainException;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testCreateUserByEmailSuccess(): void
    {
        $id = Id::next();
        $email = new Email('test@example.com');
        $name = new Name('John', 'Doe');
        $confirmToken = ConfirmToken::generate();
        $password = new Password('secret123');
        $createdAt = new DateTimeImmutable();

        $user = new UserBuilder(
            id: $id,
            name: $name,
            createdAt: $createdAt,
        )->viaSignUpEmail(
            email: $email,
            password: $password,
            confirmToken: $confirmToken
        )->build();

        $this->assertSame($id, $user->id);
        $this->assertSame($email, $user->email);
        $this->assertSame($name, $user->name);
        $this->assertSame($password, $user->password);
        $this->assertSame($createdAt, $user->createdAt);
        $this->assertSame(UserStatus::Wait, $user->status);
        $this->assertSame($confirmToken, $user->confirmToken);
        $this->assertSame(null, $user->resetToken);
        $this->assertEmpty($user->authAccounts());
    }

    public function testCreatedUserByProviderSuccess()
    {
        $id = Id::next();
        $name = new Name('John', 'Doe');
        $provider = ExternalProvider::Google;
        $externalId = 'google-123456';
        $createdAt = new DateTimeImmutable();

        $user = new UserBuilder(
            id: $id,
            name: $name,
            createdAt: $createdAt,
        )->viaSignUpExternalProvider(
            externalProvider: $provider,
            externalProviderId: $externalId
        )->build();

        $this->assertSame($id, $user->id);
        $this->assertSame(null, $user->email);
        $this->assertSame($name, $user->name);
        $this->assertSame(null, $user->password);
        $this->assertSame($createdAt, $user->createdAt);
        $this->assertSame(UserStatus::Active, $user->status);
        $this->assertSame(null, $user->confirmToken);
        $this->assertSame(null, $user->resetToken);
        $this->assertCount(1, $user->authAccounts());
    }

    public function testCreatedUserByProviderAccountAlreadyAttached(): void
    {
        $provider = ExternalProvider::Google;
        $externalId = 'google-123456';

        $user = new UserBuilder()->viaSignUpExternalProvider(
            externalProvider: $provider,
            externalProviderId: $externalId
        )->build();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Auth account already attached.');

        $user->attachExternalProvider($provider, $externalId, new DateTimeImmutable());
    }
}
