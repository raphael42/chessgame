<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

use App\Form\Contact;

class ContactController extends AbstractController
{
    public function contactfunction(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $formContact = $this->createForm(Contact::class);
        $formContact->handleRequest($request);
        if ($formContact->isSubmitted() && $formContact->isValid()) {
            $data = $formContact->getData();

            // TODO : Send the email
            $email = (new Email())
            ->from('contact@freechess.fr')
            ->to('raphael.bellon42@gmail.com')
            ->subject('Symfony mailer!')
            ->text('Text integration')
            ->html('<p>Html integration</p>');

            $result = $mailer->send($email);
            dump($result);die;
        }

        return $this->render('contact.html.twig', [
            'formContact' => $formContact->createView(),
        ]);
    }
}
