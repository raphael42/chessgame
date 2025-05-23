<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Security\Http\Attribute\CurrentUser;

use App\Form\CreateGameWithFriend;
use App\Form\CreateGameRandom;
use App\Form\CreateGameAI;
use App\Entity;

class HomeController extends AbstractController
{
    public function index(Request $request, EntityManagerInterface $entityManager, #[CurrentUser] ?Entity\User $user)
    {
        $formWithFriend = $this->createForm(CreateGameWithFriend::class);
        $formWithFriend->handleRequest($request);
        if ($formWithFriend->isSubmitted() && $formWithFriend->isValid()) {
            $data = $formWithFriend->getData();
            $data['type'] = 'with-friend'; // Game type is with friend, set it

            $url = time().bin2hex(random_bytes(10));
            $gameId = $this->createNewGame($url, $data, $entityManager, $user);

            $session = $request->getSession();
            $session->set('gameDatas', [
                'gameCreator' => true,
                'id' => $gameId,
            ]);

            return $this->redirectToRoute('game', ['url' => $url]);
        }

        $formRandom = $this->createForm(CreateGameRandom::class);
        $formRandom->handleRequest($request);
        if ($formRandom->isSubmitted() && $formRandom->isValid()) {
            $data = $formRandom->getData();

            $url = time().bin2hex(random_bytes(10));
            $gameId = $this->createNewGame($url, $data, $entityManager, $user);

            $session = $request->getSession();
            $session->set('gameDatas', [
                'gameCreator' => true,
                'id' => $gameId,
            ]);

            return $this->redirectToRoute('game', ['url' => $url]);
        }

        $formAI = $this->createForm(CreateGameAI::class);
        $formAI->handleRequest($request);
        if ($formAI->isSubmitted() && $formAI->isValid()) {
            $data = $formAI->getData();
            $data['type'] = 'against-ai'; // Game type is against AI, set it

            $data['timePerPlayer'] = null; // No time management, at least for now
            $data['secondsIncrement'] = 0; // No increment, for now

            $url = time().bin2hex(random_bytes(10));
            $gameId = $this->createNewGame($url, $data, $entityManager, $user);

            $session = $request->getSession();
            $session->set('gameDatas', [
                'gameCreator' => true,
                'id' => $gameId,
            ]);

            return $this->redirectToRoute('gameAi', ['url' => $url]);
        }

        return $this->render('index.html.twig', [
            'formWithFriend' => $formWithFriend->createView(),
            'formRandom' => $formRandom->createView(),
            'formAI' => $formAI->createView(),
        ]);
    }

    private function createNewGame(string $url, array $data, EntityManagerInterface $entityManager, ?Entity\User $user) : int
    {
        $dateTimeNow = new \DateTime();

        $timePerPlayer = isset($data['timePerPlayer']) ? $data['timePerPlayer'] * 60 : null;

        // BOF create game
        $gameEntity = new Entity\Game();
        $gameEntity->setUrl($url);
        $gameEntity->setFen('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1');
        $gameEntity->setIncrement($data['secondsIncrement']);
        $gameEntity->setTime($timePerPlayer);
        $gameEntity->setDateInsert($dateTimeNow);
        $gameEntity->setStatus('waiting-player');
        $gameEntity->setType($data['type']);
        $gameEntity->setCreatorColorChose($data['color']);
        $gameEntity->setAiLevel($data['level'] ?? null);
        $entityManager->persist($gameEntity);
        // EOF create game

        // BOF create players
        $playerCreatorEntity = new Entity\Player();
        $color = $data['color'];
        // $timePerPlayer = \DateTime::createFromFormat('H:i:s', '00:'.(string) $data['timePerPlayer'].':00');
        $timePerPlayer = $timePerPlayer;
        if ($color === 'random') {
            $test = rand(0, 1);
            $color = ($test === 0) ? 'w' : 'b';
        }
        $playerCreatorColor = $color;
        $playerCreatorEntity->setColor($color);
        $playerCreatorEntity->setGameCreator(true);
        $playerCreatorEntity->setTimeLeft($timePerPlayer);
        $playerCreatorEntity->setIdGame($gameEntity);
        $playerCreatorEntity->setUser($user);
        $entityManager->persist($playerCreatorEntity);

        $playerGuestEntity = new Entity\Player();
        $color = ($color === 'w') ? 'b' : 'w';
        $playerGuestColor = $color;
        $playerGuestEntity->setColor($color);
        $playerGuestEntity->setGameCreator(false);
        $playerGuestEntity->setTimeLeft($timePerPlayer);
        $playerGuestEntity->setIdGame($gameEntity);
        $entityManager->persist($playerGuestEntity);
        // EOF create players

        // BOF update database
        $entityManager->flush();
        // EOF update database

        return $gameEntity->getId();
    }
}
