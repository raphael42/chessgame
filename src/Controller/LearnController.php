<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

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
            'challengesUser' => $challengesUser,
        ]);
    }

    public function learnGamefunction(string $gameCategory, int $gameId, EntityManagerInterface $entityManager): Response
    {
        $challenges = $entityManager->getRepository(Entity\Challenge::class)->findAll();

        $currentChallenge = [];
        $nextChallengeExist = false; // Defines if there is a next challenge, to redirect to the next or display modal
        foreach ($challenges as $oneChallenge) {
            if ($gameId === $oneChallenge->getOrdering() && $gameCategory === $oneChallenge->getSlug()) {
                $currentChallenge = $oneChallenge;
            }

            if (($gameId + 1) === $oneChallenge->getOrdering() && $gameCategory === $oneChallenge->getSlug()) {
                $nextChallengeExist = true;
            }
        }

        return $this->render('learn-game.html.twig', [
            'challenges' => $challenges,
            'currentChallenge' => $currentChallenge,
            'nextChallengeExist' => $nextChallengeExist,
        ]);
    }

    public function ajaxSaveChallengeFunction(#[CurrentUser] ?Entity\User $user, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $content = $request->getContent();
        $data = json_decode($content, true);

        // Mandatory variables
        $requiredFields = ['category', 'id', 'nb_moves'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Variable '.$field.' is mandatory',
                ], 400);
            }
        }

        $challenge = $entityManager->getRepository(Entity\Challenge::class)->findOneBy([
            'slug' => $data['category'],
            'ordering' => $data['id'],
        ]);

        if (is_null($challenge)) {
            $response = [
                'success' => false,
                'massage' => 'Challenge not found',
            ];
            return new JsonResponse($response, 404);
        }

        $scoreDifference = $challenge->getScoreGoal() - $data['nb_moves'];

        $scoreObtained = 1; // 1 point default
        if ($scoreDifference === 0) { // Score same as the goal, 3 points
            $scoreObtained = 3;
        } elseif ($scoreDifference === -1 || $scoreDifference === -2) { // 2 moves more than the goal, 2 points
            $scoreObtained = 2;
        }

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

        if ($user !== null) { // User is connected, try to find a chanllenge already done
            $challengeUser = $entityManager->getRepository(Entity\ChallengeUser::class)->findOneBy([
                'user' => $user,
                'challenge' => $challenge,
            ]);
        }

        // User is not connected or we didn't find a chllenge, try with cookie
        if (is_null($user) || !isset($challengeUser)) {
            $challengeUser = $entityManager->getRepository(Entity\ChallengeUser::class)->findOneBy([
                'session' => $challengeCookie,
                'challenge' => $challenge,
            ]);
        }

        // No challenge found, create it
        if (is_null($challengeUser)) {
            $challengeUser = new Entity\ChallengeUser();
        }

        // Set a new score only if there is none or a lower one is saved
        if (is_null($challengeUser->getScore()) || $challengeUser->getScore() < $scoreObtained) {
            $challengeUser->setScore($scoreObtained);
        }
        $challengeUser->setChallenge($challenge);
        $challengeUser->setSession($challengeCookie);

        if ($user !== null) { // User is connected, use his account and cookie as well
            $challengeUser->setUser($user);
        }

        $entityManager->persist($challengeUser);

        $entityManager->flush();

        $response = [
            'success' => true,
        ];
        return new JsonResponse($response);
    }
}
