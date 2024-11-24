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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use \Mailjet\Resources;

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
        $session = $request->getSession();

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

            $session->set('createdAccountEmail', $form->get('email')->getData());

            // generate a signed url and email it to the user
            // $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
            //     (new TemplatedEmail())
            //         ->from(new Address('contact@chess-league.com', 'Raphael'))
            //         ->to($user->getEmail())
            //         ->subject('Please Confirm your Email')
            //         ->htmlTemplate('registration/confirmation_email.html.twig')
            // );
            // do anything else you need here, like send an email

            return $this->redirectToRoute('login', ['status' => 'account-created']);
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
            $passwordRecoveryEntity->setDone(false);

            $entityManager->persist($passwordRecoveryEntity);
            $entityManager->flush();

            $mj = new \Mailjet\Client($_SERVER['MAILJET_API_KEY'], $_SERVER['MAILJET_API_SECRET'], true, ['version' => 'v3.1']);
            $body = [
                'Messages' => [
                    [
                        'From' => [
                            'Email' => "contact@chess-league.com",
                            'Name' => "Chess-League"
                        ],
                        'To' => [
                            [
                                'Email' => $user->getEmail(),
                                'Name' => $user->getNickname(),
                            ]
                        ],
                        'TemplateID' => 6436186,
                        'TemplateLanguage' => true,
                        'Subject' => "Récupération de votre mot de passe",
                        'Variables' => [
                            'nickname' => $user->getNickname(),
                            'domaineName' => "Chess-League",
                            'token' => $randomString,
                        ],
                    ]
                ]
            ];
            $response = $mj->post(Resources::$Email, ['body' => $body]);

            return $this->redirectToRoute('passwordRecovery', ['status' => 'send']);
        }

        return $this->render('security/password-recovery.html.twig', [
            'formPasswordRecovery' => $formPasswordRecovery->createView(),
            'status' => $_GET['status'] ?? null,
        ]);
    }

    public function passwordChangefunction($url, Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $passwordRecoveryEntity = $entityManager->getRepository(Entity\PasswordRecovery::class)->findOneBy([
            'token' => $url,
        ]);

        // TODO : faire une page 404
        if (is_null($passwordRecoveryEntity)) {
            throw new NotFoundHttpException('L\'élément n\'existe pas.');
        }

        $dateTimeNow = new \DateTime();
        $diff = $dateTimeNow->diff($passwordRecoveryEntity->getDateInsert());

        // If this recovery has already been done
        if ($passwordRecoveryEntity->isDone()) {
            throw new NotFoundHttpException('La demande a déjà été traité');
        }

        // If this recovery is too old, more than 4 hours
        if ($diff->days > 0 || $diff->h > 4) {
            // throw new NotFoundHttpException('La demande a expiré');
        }

        $formPasswordChange = $this->createForm(PasswordChange::class);
        $formPasswordChange->handleRequest($request);
        if ($formPasswordChange->isSubmitted() && $formPasswordChange->isValid()) {
            $data = $formPasswordChange->getData();

            $user = $passwordRecoveryEntity->getUser();

            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $formPasswordChange->get('plainPassword')->getData()
                )
            );

            $passwordRecoveryEntity->setDone(true);
            $entityManager->persist($passwordRecoveryEntity);

            $entityManager->flush();

            return $this->redirectToRoute('login', ['status' => 'password-edited']);
        }

        return $this->render('security/password-change.html.twig', [
            'formPasswordChange' => $formPasswordChange->createView(),
        ]);
    }
}
