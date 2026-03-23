<?php

namespace App\User\UI\Http\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/users', name: 'user_list')]
    public function list(): Response
    {
        return $this->render('user/list.html.twig', [
            'users' => [
                ['email' => 'test1@mail.com'],
                ['email' => 'test2@mail.com'],
            ]
        ]);
    }
}
