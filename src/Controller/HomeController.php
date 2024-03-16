<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityManagerInterface;

use App\Form\CreateGameWithFriend;
use App\Form\CreateGameRandom;
use App\Entity;

class HomeController extends AbstractController
{
    public function index(Request $request, EntityManagerInterface $entityManager)
    {
        $formWithFriend = $this->createForm(CreateGameWithFriend::class);
        $formWithFriend->handleRequest($request);
        if ($formWithFriend->isSubmitted() && $formWithFriend->isValid()) {
            $data = $formWithFriend->getData();
            $data['type'] = 'with-friend'; // Game type is with friend, set it

            $url = time().bin2hex(random_bytes(10));
            $gameId = $this->createNewGame($url, $data, $entityManager);

            $session = new Session();
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
            $gameId = $this->createNewGame($url, $data, $entityManager);

            $session = new Session();
            $session->set('gameDatas', [
                'gameCreator' => true,
                'id' => $gameId,
            ]);

            return $this->redirectToRoute('game', ['url' => $url]);
        }

        return $this->render('index.html.twig', [
            'formWithFriend' => $formWithFriend->createView(),
            'formRandom' => $formRandom->createView(),
        ]);
    }

    private function createNewGame(string $url, array $data, EntityManagerInterface $entityManager) : int
    {
        $dateTimeNow = new \DateTime();

        // BOF create game
        $gameEntity = new Entity\Game();
        $gameEntity->setUrl($url);
        $gameEntity->setFen('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1');
        $gameEntity->setIncrement($data['secondsIncrement']);
        $gameEntity->setTime($data['timePerPlayer'] * 60);
        $gameEntity->setDateInsert($dateTimeNow);
        $gameEntity->setStatus('waiting-player');
        $gameEntity->setType($data['type']);
        $entityManager->persist($gameEntity);
        // EOF create game

        // BOF create players
        $playerCreatorEntity = new Entity\Player();
        $color = $data['color'];
        // $timePerPlayer = \DateTime::createFromFormat('H:i:s', '00:'.(string) $data['timePerPlayer'].':00');
        $timePerPlayer = $data['timePerPlayer'] * 60;
        if ($color === 'random') {
            $test = rand(0, 1);
            $color = ($test === 0) ? 'white' : 'black';
        }
        $playerCreatorColor = $color;
        $playerCreatorEntity->setColor($color);
        $playerCreatorEntity->setGameCreator(true);
        $playerCreatorEntity->setTimeLeft($timePerPlayer);
        $playerCreatorEntity->setIdGame($gameEntity);
        $entityManager->persist($playerCreatorEntity);

        $playerGuestEntity = new Entity\Player();
        $color = ($color === 'white') ? 'black' : 'white';
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
