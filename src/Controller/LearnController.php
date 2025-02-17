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

        $challengesAdvancement = [];
        $totalStars = 0;
        foreach ($challenges as $oneChallenge) {
            if (!isset($challengesAdvancement[$oneChallenge->getSlug()][$oneChallenge->getOrdering()])) {
                $challengesAdvancement[$oneChallenge->getSlug()][$oneChallenge->getOrdering()] = null;
            }

            foreach ($challengesUser as $oneChallengeUser) {
                if ($oneChallenge->getId() === $oneChallengeUser->getChallenge()->getId()) {
                    $challengesAdvancement[$oneChallenge->getSlug()][$oneChallenge->getOrdering()] = $oneChallengeUser->getScore();
                    $totalStars += $oneChallengeUser->getScore();
                }
            }
        }

        $challengeNumberLink = [];
        $finishedChallengesResult = [];
        $ongoingChallengesAdvancement = [];
        foreach ($challengesAdvancement as $oneSlug => $challengesScores) {
            $nbChallengesDone = 0;
            foreach ($challengesScores as $oneScore) {
                if (!is_null($oneScore)) {
                    $nbChallengesDone++;
                }
            }

            $challengeNumberLink[$oneSlug] = array_key_first(array_filter($challengesScores, function($value) {
                return $value === null;
            }));

            if (is_null($challengeNumberLink[$oneSlug])) {
                $challengeNumberLink[$oneSlug] = 1;
            }

            if (count($challengesScores) === $nbChallengesDone) { // All challenges are done, calculate average score
                $finishedChallengesResult[$oneSlug] = floor(array_sum($challengesScores) / count($challengesScores));
            } elseif ($nbChallengesDone > 0) { // Not all challenges done, check how much it misses
                $ongoingChallengesAdvancement[$oneSlug] = $nbChallengesDone.' / '.count($challengesScores);
            }
        }

        return $this->render('learn.html.twig', [
            'challenges' => $challenges,
            'challengesUser' => $challengesUser,
            'ongoingChallengesAdvancement' => $ongoingChallengesAdvancement,
            'finishedChallengesResult' => $finishedChallengesResult,
            'challengeNumberLink' => $challengeNumberLink,
            'progressPercent' => round(($totalStars / (count($challenges) * 3)) * 100),
        ]);
    }

    public function learnGamefunction(#[CurrentUser] ?Entity\User $user, Request $request, string $gameCategory, int $gameId, EntityManagerInterface $entityManager): Response
    {
        $challenges = $entityManager->getRepository(Entity\Challenge::class)->findAll();

        // If user is connected, use the user
        if ($user !== null) {
            $challengesUser = $entityManager->getRepository(Entity\ChallengeUser::class)->findBy([
                'user' => $user,
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

        $challengesAdvancement = [];
        foreach ($challenges as $oneChallenge) {
            if (!isset($challengesAdvancement[$oneChallenge->getSlug()][$oneChallenge->getOrdering()])) {
                $challengesAdvancement[$oneChallenge->getSlug()][$oneChallenge->getOrdering()] = null;
            }

            foreach ($challengesUser as $oneChallengeUser) {
                if ($oneChallenge->getId() === $oneChallengeUser->getChallenge()->getId()) {
                    $challengesAdvancement[$oneChallenge->getSlug()][$oneChallenge->getOrdering()] = $oneChallengeUser->getScore();
                }
            }
        }

        $challengeNumberLink = [];
        foreach ($challengesAdvancement as $oneSlug => $challengesScores) {
            $challengeNumberLink[$oneSlug] = array_key_first(array_filter($challengesScores, function($value) {
                return $value === null;
            }));

            if (is_null($challengeNumberLink[$oneSlug])) {
                $challengeNumberLink[$oneSlug] = 1;
            }
        }

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
            'challengesAdvancement' => $challengesAdvancement,
            'currentChallenge' => $currentChallenge,
            'nextChallengeExist' => $nextChallengeExist,
            'challengeNumberLink' => $challengeNumberLink,
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

        // User is not connected or we didn't find a challenge, try with cookie
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
