<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

use App\Form\Contact;
use App\Entity;

class ContactController extends AbstractController
{
    public function contactfunction(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {

        $session = $request->getSession();

        if (is_null($session->get('captchaCodeContact')) || is_null($session->get('captchaSessionContact')) || is_null($session->get('captchaContactTimeStamp')) || $session->get('captchaContactTimeStamp') < time() - 3600) {
            $imageWidth = 80;
            $imageLength = 25;
            $caracters = 'ABCDEF123456789';

            // Create picture
            $image = imagecreatetruecolor($imageWidth, $imageLength);

            // Set white background (255,255,255)
            imagefilledrectangle($image, 0, 0, $imageWidth, $imageLength, imagecolorallocate($image, 255, 255, 255));

            // We had 10 colored lines to set captcha more difficult to read
            for ($i = 0; $i <= 10; $i++){
                // Get a random hexadecimal color by choosing randomly 6 caracters
                $hexaNumber = substr(str_shuffle('ABCDEF0123456789'), 0, 6);

                // Convert hexadecimal value to rgb
                $rgb = [
                    'r' => hexdec(substr($hexaNumber,0,2)),
                    'g' => hexdec(substr($hexaNumber,2,2)),
                    'b' => hexdec(substr($hexaNumber,4,2))
                ];

                // We print the line
                imageline(
                    $image,
                    rand(1, $imageWidth - 25),
                    rand(1, $imageLength),
                    rand(1, $imageWidth + 25),
                    rand(1, $imageLength),
                    imagecolorallocate($image, $rgb['r'], $rgb['g'], $rgb['b'])
                );
            }

            // Get 4 caracters to create the code
            $captchaCode = substr(str_shuffle($caracters), 0, 4);
            $captchaSession = substr(str_shuffle($caracters), 0, 15);

            // Save the code in session var
            $session->set('captchaCodeContact', $captchaCode);
            $session->set('captchaSessionContact', $captchaSession);
            $session->set('captchaContactTimeStamp', time());

            // Display the code in image by adding one space between each caracters
            $imgCodeWithSpaces = implode(' ', str_split($captchaCode));

            // Write the code inside the picture
            imagestring($image, 5, 10, 5, $imgCodeWithSpaces, imagecolorallocate($image, 0, 0, 0));

            // Create and save the picture in the appropriate directory
            imagepng($image, 'assets/img/captcha/'.$captchaSession.'.png');

            // Now that image is saved, destroy it to not use memory place
            imagedestroy($image);
        }

        $options = [
            'captchaCode' => $session->get('captchaCodeContact'),
            'captchaSession' => $session->get('captchaSessionContact'),
        ];

        $formContact = $this->createForm(Contact::class, null, $options);
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

            // TODO : ajouter un captcha avant ca
            // $email = (new Email())
            // ->from('contact@chess-league.com')
            // ->to('raphael.bellon42@gmail.com')
            // ->subject($data['subject'])
            // ->text($data['message']);

            // try {
            //     $mailer->send($email);
            // } catch (TransportExceptionInterface $e) {
            // }
        }

        return $this->render('contact.html.twig', [
            'formContact' => $formContact->createView(),
            'status' => $_GET['status'] ?? null,
        ]);
    }
}
