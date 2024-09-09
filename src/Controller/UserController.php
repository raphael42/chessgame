<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

use App\Form\Contact;
use App\Entity;

class UserController extends AbstractController
{
    public function loginfunction(Request $request, EntityManagerInterface $entityManager): Response
    {
        $formLogin = $this->createForm(Login::class);
        $formLogin->handleRequest($request);
        if ($formLogin->isSubmitted() && $formLogin->isValid()) {
            // TODO
        }

        $formRegister = $this->createForm(Register::class);
        $formRegister->handleRequest($request);
        if ($formRegister->isSubmitted() && $formRegister->isValid()) {
            // TODO
        }

        return $this->render('login.html.twig', [
            'formLogin' => $formLogin->createView(),
            'formRegister' => $formRegister->createView(),
            'status' => $_GET['status'] ?? null,
        ]);
    }
}
