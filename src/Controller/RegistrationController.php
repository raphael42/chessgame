<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

use App\Form\PasswordRecovery;
use App\Form\PasswordChange;
use App\Entity;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    public function registerfunction(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dateTimeNow = new \DateTime();

            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setNickname($form->get('nickname')->getData());
            $user->setDateInsert($dateTimeNow);

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            // $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
            //     (new TemplatedEmail())
            //         ->from(new Address('contact@chess-league.com', 'Raphael'))
            //         ->to($user->getEmail())
            //         ->subject('Please Confirm your Email')
            //         ->htmlTemplate('registration/confirmation_email.html.twig')
            // );
            // do anything else you need here, like send an email

            return $this->redirectToRoute('login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    {
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('register');
    }

    public function passwordRecoveryfunction(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $formPasswordRecovery = $this->createForm(PasswordRecovery::class);
        $formPasswordRecovery->handleRequest($request);

        if ($formPasswordRecovery->isSubmitted() && $formPasswordRecovery->isValid()) {
            // For the moment, save the data in DB. Later, send an email
            $data = $formPasswordRecovery->getData();

            // Get the user with current email.
            $user = $entityManager->getRepository(Entity\User::class)->findOneBy([
                'email' => $data['email'],
            ]);

            // No user found, return status send like it worked to not give information that the email exists or not
            if (is_null($user)) {
                return $this->redirectToRoute('passwordRecovery', ['status' => 'send']);
            }

            // User found, continue the process
            $dateTimeNow = new \DateTime();

            $bytes = random_bytes(20);
            $randomString = bin2hex($bytes);

            $passwordRecoveryEntity = new Entity\PasswordRecovery();
            $passwordRecoveryEntity->setToken($randomString);
            $passwordRecoveryEntity->setDateInsert($dateTimeNow);
            $passwordRecoveryEntity->setUser($user);

            $entityManager->persist($passwordRecoveryEntity);
            $entityManager->flush();

            return $this->redirectToRoute('passwordRecovery', ['status' => 'send']);

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

        return $this->render('security/password-recovery.html.twig', [
            'formPasswordRecovery' => $formPasswordRecovery->createView(),
            'status' => $_GET['status'] ?? null,
        ]);
    }

    public function passwordChangefunction($url, Request $request, EntityManagerInterface $entityManager): Response
    {
        $passwordRecoveryEntity = $entityManager->getRepository(Entity\PasswordRecovery::class)->findOneBy([
            'token' => $url,
        ]);

        // TODO : faire une page 404
        if (is_null($passwordRecoveryEntity)) {
            header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
            die;
        }

        $formPasswordChange = $this->createForm(PasswordChange::class);
        $formPasswordChange->handleRequest($request);

        if ($formPasswordChange->isSubmitted() && $formPasswordChange->isValid()) {
        }

        return $this->render('security/password-change.html.twig', [
            'formPasswordChange' => $formPasswordChange->createView(),
            'status' => $_GET['status'] ?? null,
        ]);
    }
}
