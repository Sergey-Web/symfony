<?php

declare(strict_types=1);

namespace App\User\Domain\ValueObject;

use Webmozart\Assert\Assert;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final readonly class Name
{
    #[ORM\Column(name: 'first_name', nullable: false)]
    public string $firstName;

    #[ORM\Column(name: 'last_name', nullable: true)]
    public ?string $lastName;

    public function __construct(
        string $firstName,
        ?string $lastName
    ) {
        Assert::notEmpty($firstName);
        Assert::lengthBetween($firstName, 2, 50);

        if ($lastName !== null && $lastName !== '') {
            Assert::lengthBetween($lastName, 2, 50);
        }

        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }
}
