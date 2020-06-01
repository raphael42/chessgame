<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

use App\Form\GameCreationType;
use App\Entity;

class HomeController extends AbstractController
{
    public function index(Request $request)
    {
        $form = $this->createForm(GameCreationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $url = bin2hex(random_bytes(10));
            $this->createNewGame($url, $data);

            $session = new Session();
            // $session->start();
            $session->set('gameCreator', true);

            return $this->redirectToRoute('game-second', ['url' => $url]);
        }

        return $this->render('index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function createNewGame(string $url, array $data) : void
    {
        $entityManager = $this->getDoctrine()->getManager();

        // BOF create game
        $gameEntity = new Entity\Game();
        $gameEntity->setUrl($url);
        $gameEntity->setFen('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1');
        $gameEntity->setIncrement($data['secondsIncrement']);
        $entityManager->persist($gameEntity);
        // EOF create game

        // BOF create players
        $playerCreatorEntity = new Entity\Player();
        $color = $data['color'];
        $timePerPlayer = $data['timePerPlayer'];
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

        return;
    }
}
