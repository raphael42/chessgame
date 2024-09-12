<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserAccountController extends AbstractController
{
    public function userAccountFunction(): Response
    {
        return $this->render('account.html.twig', [
            'controller_name' => 'UserAccountController',
        ]);
    }
}
