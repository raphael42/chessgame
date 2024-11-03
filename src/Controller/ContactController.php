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

        //largeur de l'image
        $largeur = 80;
        //hauteur de l'image
        $hauteur = 25;
        //nombre de lignes multicolore qui seront affichées avec le code (10 est bien)
        $lignes = 10;
        //type de caractère du code qui sera affiché dans l'image
        $caracteres = "ABCDEF123456789";
        //on crée l'image rectangle
        $image = imagecreatetruecolor($largeur, $hauteur);
        //on met un fond en blanc (255,255,255)
        imagefilledrectangle($image, 0, 0, $largeur, $hauteur, imagecolorallocate($image, 255, 255, 255));
        //on ajoute les lignes
        //fonction qui permet de retourner la valeur en RGB d'une couleur hexadécimale
        function hexargb($hex){
        //on retourne la valeur sous forme d'array R, G et B
        return [
            'r' => hexdec(substr($hex,0,2)),
            'g' => hexdec(substr($hex,2,2)),
            'b' => hexdec(substr($hex,4,2))
        ];
        }
        //ajoute les lignes de différentes couleurs au fond blanc pour mettre de la difficulté
        for($i = 0; $i <= $lignes; $i++){
            //choisi une couleur aléatoirement (str_shuffle), de 6 caractères (substr(chaine,0,6)) avec la sélection alphanumérique
            $rgb = hexargb(substr(str_shuffle("ABCDEF0123456789"),0,6));

            imageline(
                $image,
                rand(1, $largeur - 25),
                rand(1, $hauteur),
                rand(1, $largeur + 25),
                rand(1, $hauteur),
                imagecolorallocate($image, $rgb['r'], $rgb['g'], $rgb['b'])
            );
        }
        //Création du code, récupère 4 caractère aléatoirement depuis $caracteres
        $code_session = substr(str_shuffle($caracteres), 0, 4);
        //on enregistre le code dans une session pour vérifier ensuite se qu'à entré le visiteur est identique dans le traitement du formulaire
        $_SESSION['code'] = $code_session;
        // préparation du code qui va être affiché
        $code = '';
        for($i = 0; $i <= strlen($code_session); $i ++){

        //on rajoute des espace entre chaque lettre ou chiffre pour faire plus aéré (notez le "." devant "=" qui permettra d'ajouter un caractère après l'autre à $code)
        $code .= substr($code_session, $i, 1) . ' ';

        }
        //on écrit le code dans le rectangle
        imagestring($image, 5, 10, 5, $code, imagecolorallocate($image, 0, 0, 0));
        // //on affiche l'image
        imagepng($image, 'assets/img/captcha/'.$code_session.'.png');
        // //puis on détruit l'image pour libérer de l'espace
        // imagedestroy($image);


        $options = [
            'code_session' => 'C639',
        ];

        $formContact = $this->createForm(Contact::class, null, $options);
        $formContact->handleRequest($request);
        if ($formContact->isSubmitted() && $formContact->isValid()) {

            die('ok');
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
