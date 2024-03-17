<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

use App\Form\Contact;

class ContactController extends AbstractController
{
    public function contactfunction(Request $request, EntityManagerInterface $entityManager): Response
    {
        $formContact = $this->createForm(Contact::class);
        $formContact->handleRequest($request);
        if ($formContact->isSubmitted() && $formContact->isValid()) {
            $data = $formContact->getData();

            // TODO : Send the email
        }

        return $this->render('contact.html.twig', [
            'formContact' => $formContact->createView(),
        ]);
    }
}
