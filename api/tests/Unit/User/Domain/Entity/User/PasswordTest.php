<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Domain\Entity\User;

use App\Tests\Builder\User\UserBuilder;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\ResetToken;
use DateMalformedStringException;
use DateTimeImmutable;
use DomainException;
use PHPUnit\Framework\TestCase;

class PasswordTest extends TestCase
{
    private DateTimeImmutable $now;

    protected function setUp(): void
    {
        $this->now = new DateTimeImmutable();
    }

    /**
     * @throws DateMalformedStringException
     */
    public function testRequestPasswordResetAccess(): void
    {
        $user = new UserBuilder()->viaSignUpEmail()->confirmed()->build();
        $resetToken = new ResetToken('reset-token', $this->now);

        $user->requestPasswordReset($resetToken);

        $this->assertSame($resetToken, $user->resetToken);
    }

    /**
     * @throws DateMalformedStringException
     */
    public function testRequestPasswordResetEmailNotSet(): void
    {
        $resetToken = new ResetToken('reset-token', $this->now);

        $user = new UserBuilder()->viaSignUpExternalProvider()->build();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Email not set.');

        $user->requestPasswordReset($resetToken);
    }

    /**
     * @throws DateMalformedStringException
     */
    public function testRequestPasswordResetUserIsNotActive(): void
    {
        $resetToken = new ResetToken('reset-token', $this->now);
        $email = new Email('test@example.com');

        $user = new UserBuilder()->viaSignUpEmail()->confirmed()->build();

        $user->changeEmail($email);

        $user->block();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('User is not active.');

        $user->requestPasswordReset($resetToken);
    }

    public function testRequestPasswordResetUserIsAlreadyBlocked(): void
    {
        $user = new UserBuilder()->viaSignUpEmail()->confirmed()->build();

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
        $resetToken = new ResetToken('reset-token', $this->now);
        $requestData = $this->now->modify('+2 hour');

        $user = new UserBuilder()->viaSignUpEmail()->confirmed()->build();

        $user->requestPasswordReset($resetToken);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Reset token is expired.');

        $user->resetPassword($user->password, $requestData);
    }

    /**
     * @throws DateMalformedStringException
     */
    public function testPasswordResetTokenIsTooEarly(): void
    {
        $resetToken = new ResetToken('reset-token', $this->now);
        $requestData = $this->now->modify('+3 minutes');

        $user = new UserBuilder()->viaSignUpEmail()->confirmed()->build();

        $user->requestPasswordReset($resetToken);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Reset token is too early.');

        $user->resetPassword($user->password, $requestData);
    }
}
