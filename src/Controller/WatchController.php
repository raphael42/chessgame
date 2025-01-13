<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WatchController extends AbstractController
{
    public function watchfunction(): Response
    {
        return $this->render('watch.html.twig', [
        ]);
    }
}
