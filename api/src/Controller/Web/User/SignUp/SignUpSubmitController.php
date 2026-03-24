<?php

declare(strict_types=1);

namespace App\Controller\Web\User\SignUp;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SignUpSubmitController extends AbstractController
{
    #[Route('/signup', name: 'signup_submit', methods: ['POST'])]
    public function __invoke(): Response
    {
        
    }
}
