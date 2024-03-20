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
use App\Entity;

class ContactController extends AbstractController
{
    public function contactfunction(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $formContact = $this->createForm(Contact::class);
        $formContact->handleRequest($request);
        if ($formContact->isSubmitted() && $formContact->isValid()) {
            // For the moment, save the data in DB. Later, send an email
            $data = $formContact->getData();

            $dateTimeNow = new \DateTime();

            $contactFormEntity = new Entity\ContactForm();
            $contactFormEntity->setName($data['name']);
            $contactFormEntity->setSubject($data['subject']);
            $contactFormEntity->setEmail($data['email']);
            $contactFormEntity->setMessage($data['message']);
            $contactFormEntity->setDateInsert($dateTimeNow);
            $contactFormEntity->setStatus('waiting');

            $entityManager->persist($contactFormEntity);
            $entityManager->flush();

            return $this->redirectToRoute('contact', ['status' => 'send']);

            // TODO later : Send the email
            // $email = (new Email())
            // ->from('contact@freechess.fr')
            // ->to('raphael.bellon42@gmail.com')
            // ->subject('Symfony mailer!')
            // ->text('Text integration')
            // ->html('<p>Html integration</p>');

            // $result = $mailer->send($email);
            // dump($result);die;
        }

        return $this->render('contact.html.twig', [
            'formContact' => $formContact->createView(),
            'status' => $_GET['status'] ?? null,
        ]);
    }
}
