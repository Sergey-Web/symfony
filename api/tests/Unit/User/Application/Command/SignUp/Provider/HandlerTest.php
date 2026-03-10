<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Application\Command\SignUp\Provider;

use App\User\Application\Command\SignUp\Provider\Command;
use App\User\Application\Command\SignUp\Provider\Handler;
use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\Service\Flusher;
use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase
{
    public function testSuccess(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $flusher = $this->createMock(Flusher::class);

        $command = new Command(provider: 'google', externalId: '1234567890', firstName: 'John', lastName: 'Doe');

        $userRepository->expects(self::once())->method('add')->with(self::isInstanceOf(User::class));
        $flusher->expects(self::once())->method('flush');

        $handler = new Handler($userRepository, $flusher);
        $handler->handle($command);
    }
}
