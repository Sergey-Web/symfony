<?php

declare(strict_types=1);

namespace App\Controller\Web\User\Login;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LoginController extends AbstractController
{
    #[Route('/login', name: 'login', methods: ['GET'])]
    public function __invoke(): Response
    {
        return $this->render('user/login.html.twig', ['test']);
    }
}
