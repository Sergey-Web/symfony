<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Domain\Entity;

use App\User\Domain\Entity\User;
use App\User\Domain\Enum\ExternalProvider;
use App\User\Domain\Enum\UserStatus;
use App\User\Domain\ValueObject\ConfirmToken;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Id;
use App\User\Domain\ValueObject\Name;
use App\User\Domain\ValueObject\Password;
use App\User\Domain\ValueObject\ResetToken;
use DateMalformedStringException;
use DateTimeImmutable;
use DomainException;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    protected Password $password;

    private(set) DateTimeImmutable $now;

    public function setUp(): void
    {
        $this->now = new DateTimeImmutable();
        $this->password = new Password('password1234');
    }

    public function testCreateUserByEmailSuccess(): void
    {
        $id = Id::next();
        $email = new Email('test@example.com');
        $name = new Name('John', 'Doe');
        $confirmToken = ConfirmToken::generate();

        $user = User::signUpWithEmail(
            id: $id,
            email: $email,
            name: $name,
            confirmToken: $confirmToken,
            password: $this->password,
            createdAt: $this->now,
        );

        $this->assertSame($id, $user->id);
        $this->assertSame($email, $user->email);
        $this->assertSame($name, $user->name);
        $this->assertSame($this->password, $user->password);
        $this->assertSame($this->now, $user->createdAt);
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


        $user = User::signUpWithExternalProvider(
            id: $id,
            provider: $provider,
            externalId: $externalId,
            name: $name,
            createdAt: $this->now,
        );

        $this->assertSame($id, $user->id);
        $this->assertSame(null, $user->email);
        $this->assertSame($name, $user->name);
        $this->assertSame(null, $user->password);
        $this->assertSame($this->now, $user->createdAt);
        $this->assertSame(UserStatus::Active, $user->status);
        $this->assertSame(null, $user->confirmToken);
        $this->assertSame(null, $user->resetToken);
        $this->assertCount(1, $user->authAccounts());
    }

    public function testCreatedUserByProviderAccountAlreadyAttached(): void
    {
        $id = Id::next();
        $name = new Name('John', 'Doe');
        $provider = ExternalProvider::Google;
        $externalId = 'google-123456';


        $user = User::signUpWithExternalProvider(
            id: $id,
            provider: $provider,
            externalId: $externalId,
            name: $name,
            createdAt: $this->now,
        );

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Auth account already attached.');

        $user->attachExternalProvider($provider, $externalId, $this->now);
    }

    public function testConfirmSignUpSuccess(): void
    {
        $id = Id::next();
        $email = new Email('test@example.com');
        $name = new Name('John', 'Doe');
        $confirmToken = ConfirmToken::generate();

        $user = User::signUpWithEmail(
            id: $id,
            email: $email,
            name: $name,
            confirmToken: $confirmToken,
            password: $this->password,
            createdAt: $this->now,
        );

        $user->confirmSignUp($confirmToken);

        $this->assertSame(UserStatus::Active, $user->status);
    }

    public function testConfirmSignUpUserAlreadyConfirmed(): void
    {
        $id = Id::next();
        $email = new Email('test@example.com');
        $name = new Name('John', 'Doe');
        $confirmToken = ConfirmToken::generate();

        $user = User::signUpWithEmail(
            id: $id,
            email: $email,
            name: $name,
            confirmToken: $confirmToken,
            password: $this->password,
            createdAt: $this->now,
        );

        $user->confirmSignUp($confirmToken);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('User already confirmed.');

        $user->confirmSignUp($confirmToken);
    }

    public function testConfirmSignUpInvalidConfirmToken(): void
    {
        $id = Id::next();
        $email = new Email('test@example.com');
        $name = new Name('John', 'Doe');
        $confirmToken = ConfirmToken::generate();

        $user = User::signUpWithEmail(
            id: $id,
            email: $email,
            name: $name,
            confirmToken: $confirmToken,
            password: $this->password,
            createdAt: $this->now,
        );

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid confirm token.');

        $user->confirmSignUp(ConfirmToken::generate());
    }

    public function testRequestPasswordResetAccess(): void
    {
        $id = Id::next();
        $email = new Email('test@example.com');
        $name = new Name('John', 'Doe');
        $confirmToken = ConfirmToken::generate();
        $resetToken = new ResetToken('reset-token', $this->now);

        $user = User::signUpWithEmail(
            id: $id,
            email: $email,
            name: $name,
            confirmToken: $confirmToken,
            password: $this->password,
            createdAt: $this->now,
        );

        $user->confirmSignUp($confirmToken);

        $user->requestPasswordReset($resetToken);

        $this->assertSame($resetToken, $user->resetToken);
    }

    public function testRequestPasswordResetEmailNotSet(): void
    {
        $id = Id::next();
        $name = new Name('John', 'Doe');
        $provider = ExternalProvider::Google;
        $externalId = 'google-123456';
        $resetToken = new ResetToken('reset-token', $this->now);


        $user = User::signUpWithExternalProvider(
            id: $id,
            provider: $provider,
            externalId: $externalId,
            name: $name,
            createdAt: $this->now,
        );

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Email not set.');

        $user->requestPasswordReset($resetToken);
    }

    public function testRequestPasswordResetUserIsNotActive(): void
    {
        $id = Id::next();
        $name = new Name('John', 'Doe');
        $provider = ExternalProvider::Google;
        $externalId = 'google-123456';
        $resetToken = new ResetToken('reset-token', $this->now);
        $email = new Email('test@example.com');

        $user = User::signUpWithExternalProvider(
            id: $id,
            provider: $provider,
            externalId: $externalId,
            name: $name,
            createdAt: $this->now,
        );

        $user->changeEmail($email);

        $user->block();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('User is not active.');

        $user->requestPasswordReset($resetToken);
    }

    public function testRequestPasswordResetUserIsAlreadyBlocked(): void
    {
        $id = Id::next();
        $email = new Email('test@example.com');
        $name = new Name('John', 'Doe');
        $confirmToken = ConfirmToken::generate();

        $user = User::signUpWithEmail(
            id: $id,
            email: $email,
            name: $name,
            confirmToken: $confirmToken,
            password: $this->password,
            createdAt: $this->now,
        );

        $user->confirmSignUp($confirmToken);

        $user->block();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('User is already blocked.');

        $user->block();
    }

    /**
     * @throws DateMalformedStringException
     */
    public function testPasswordResetTokenIsExpired(): void
    {
        $id = Id::next();
        $email = new Email('test@example.com');
        $name = new Name('John', 'Doe');
        $confirmToken = ConfirmToken::generate();
        $resetToken = new ResetToken('reset-token', $this->now);
        $requestData = $this->now->modify('+2 hour');

        $user = User::signUpWithEmail(
            id: $id,
            email: $email,
            name: $name,
            confirmToken: $confirmToken,
            password: $this->password,
            createdAt: $this->now,
        );

        $user->confirmSignUp($confirmToken);

        $user->requestPasswordReset($resetToken);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Reset token is expired.');

        $user->resetPassword($this->password, $requestData);
    }

    /**
     * @throws DateMalformedStringException
     */
    public function testPasswordResetTokenIsTooEarly(): void
    {
        $id = Id::next();
        $email = new Email('test@example.com');
        $name = new Name('John', 'Doe');
        $confirmToken = ConfirmToken::generate();
        $resetToken = new ResetToken('reset-token', $this->now);
        $requestData = $this->now->modify('+3 minutes');

        $user = User::signUpWithEmail(
            id: $id,
            email: $email,
            name: $name,
            confirmToken: $confirmToken,
            password: $this->password,
            createdAt: $this->now,
        );

        $user->confirmSignUp($confirmToken);

        $user->requestPasswordReset($resetToken);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Reset token is too early.');

        $user->resetPassword($this->password, $requestData);
    }
}
