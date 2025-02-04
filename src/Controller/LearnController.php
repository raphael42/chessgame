<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Security\Http\Attribute\CurrentUser;

use App\Entity;

class LearnController extends AbstractController
{
    public function learnfunction(#[CurrentUser] ?Entity\User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        $challenges = $entityManager->getRepository(Entity\Challenge::class)->findAll();

        // If user is connected, use the user
        if ($user !== null) {
            $challengesUser = $entityManager->getRepository(Entity\ChallengeUser::class)->findBy([
                'user' => $user
            ]);
        } else {
            // If not, use a cookie
            $challengeCookie = $request->cookies->get('challenge-cookie');

            // The cookie doesn't exists, create one
            if (is_null($challengeCookie)) {
                $challengeCookie = time().bin2hex(random_bytes(5));

                setcookie('challenge-cookie', $challengeCookie, [
                    'expires' => time() + (20 * 365 * 24 * 60 * 60), // expiration : 20 years to not expire it
                    'path' => '/',
                    'samesite' => 'Lax',
                ]);
            }

            $challengesUser = $entityManager->getRepository(Entity\ChallengeUser::class)->findBy([
                'session' => $challengeCookie
            ]);
        }

        return $this->render('learn.html.twig', [
            'controller_name' => 'LearnController',
        ]);
    }

    public function learnGamefunction(): Response
    {
        return $this->render('learn.html.twig', [
            'controller_name' => 'LearnController',
        ]);
    }
}
