<?php

declare(strict_types=1);

namespace App\Controller\Web\User\SignUp;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SignUpController extends AbstractController
{
    #[Route('/signup', name: 'signup', methods: ['GET'])]
    public function __invoke(): Response
    {
        return $this->render('user/signup.html.twig');
    }
}
