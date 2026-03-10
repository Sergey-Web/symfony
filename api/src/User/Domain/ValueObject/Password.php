<?php

declare(strict_types=1);

namespace App\User\Domain\ValueObject;

use Doctrine\ORM\Mapping as ORM;

final readonly class Password
{
    #[ORM\Column(name: 'password_hash', type: 'string', nullable: true)]
    private(set) string $hash;

    public function __construct(
        string $password,
    ) {
        self::validate($password);

        $this->hash = password_hash($password, PASSWORD_DEFAULT);
    }

    private static function validate(string $value): void
    {
        if (strlen($value) < 8) {
            throw new \InvalidArgumentException('Password must be at least 8 characters long.');
        }
    }

    public function verify(string $password): bool
    {
        return password_verify($password, $this->hash);
    }
}
