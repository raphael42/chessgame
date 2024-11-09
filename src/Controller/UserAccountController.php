<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use Symfony\Component\Security\Http\Attribute\CurrentUser;

use App\Form\PersonalInformations;
use App\Form\PasswordEdit;
use App\Entity;

class UserAccountController extends AbstractController
{
    public function userAccountFunction(): Response
    {
        return $this->render('profile/account.html.twig', []);
    }

    public function userAccountInfosPersoFunction(#[CurrentUser] ?Entity\User $user, UserPasswordHasherInterface $userPasswordHasher, Request $request, EntityManagerInterface $entityManager): Response
    {
        $options = [
            'email' => $user->getEmail(),
            'nickname' => $user->getNickname(),
        ];

        $formPersonalInformations = $this->createForm(PersonalInformations::class, null, $options);
        $formPersonalInformations->handleRequest($request);
        if ($formPersonalInformations->isSubmitted() && $formPersonalInformations->isValid()) {
            $data = $formPersonalInformations->getData();

            $user->setNickname($data['nickname']);
            $user->setEmail($data['email']);

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('profileInfoPerso', ['status' => 'infos-edited']);
        }

        $formPasswordEdit = $this->createForm(PasswordEdit::class);
        $formPasswordEdit->handleRequest($request);
        if ($formPasswordEdit->isSubmitted() && $formPasswordEdit->isValid()) {
            $data = $formPasswordEdit->getData();

            // Current user password false, do not allow the password change
            if (!password_verify($data['oldPassword'], $user->getPassword())) {
                return $this->redirectToRoute('profileInfoPerso', ['status' => 'error-current-password']);
            }

            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $formPasswordEdit->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('profileInfoPerso', ['status' => 'password-edited']);
        }

        return $this->render('profile/infos-perso.html.twig', [
            'formPersonalInformations' => $formPersonalInformations->createView(),
            'formPasswordEdit' => $formPasswordEdit->createView(),
            'status' => $_GET['status'] ?? null,
        ]);
    }

    public function userAccountGameHistoryFunction(#[CurrentUser] ?Entity\User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        $gamePlayers = $entityManager->getRepository(Entity\Player::class)->findBy([
            'user' => $user,
        ]);

        return $this->render('profile/games-history.html.twig', [
            'gamePlayers' => $gamePlayers
        ]);
    }
}
