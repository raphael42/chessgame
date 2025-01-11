<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LearnController extends AbstractController
{
    public function learnfunction(): Response
    {
        return $this->render('learn.html.twig', [
            'controller_name' => 'LearnController',
        ]);
    }

    public function learnGamefunction(): Response
    {
        return $this->render('learn.html.twig', [
            'controller_name' => 'LearnController',
        ]);
    }
}
