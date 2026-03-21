<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Domain\Entity\User;

use App\Tests\Builder\User\UserBuilder;
use App\User\Domain\Enum\UserStatus;
use App\User\Domain\ValueObject\ConfirmToken;
use DomainException;
use PHPUnit\Framework\TestCase;

class ConfirmSignUpTest extends TestCase
{
    public function testConfirmSignUpSuccess(): void
    {
        $confirmToken = ConfirmToken::generate();
        $user = new UserBuilder()->viaSignUpEmail(confirmToken: $confirmToken)->build();

        $user->confirmSignUp($confirmToken);
        $this->assertSame(UserStatus::Active, $user->status);
    }

    public function testConfirmSignUpUserAlreadyConfirmed(): void
    {
        $confirmToken = ConfirmToken::generate();
        $user = new UserBuilder()->viaSignUpEmail(confirmToken: $confirmToken)->build();
        $user->confirmSignUp($confirmToken);
        $this->assertSame(UserStatus::Active, $user->status);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('User already confirmed.');

        $user->confirmSignUp($confirmToken);
    }

    public function testConfirmSignUpInvalidConfirmToken(): void
    {
        $confirmToken = ConfirmToken::generate();
        $user = new UserBuilder()->viaSignUpEmail(confirmToken: $confirmToken)->build();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid confirm token.');

        $user->confirmSignUp(ConfirmToken::generate());
    }
}
