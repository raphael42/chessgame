<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

use App\Form\Login;
use App\Entity;

class LoginController extends AbstractController
{
    public function loginfunction(Request $request, EntityManagerInterface $entityManager): Response
    {
        $formLogin = $this->createForm(Login::class);
        $formLogin->handleRequest($request);
        if ($formLogin->isSubmitted() && $formLogin->isValid()) {
            // TODO
        }

        return $this->render('login.html.twig', [
            'formLogin' => $formLogin->createView(),
            'status' => $_GET['status'] ?? null,
        ]);
    }
}
