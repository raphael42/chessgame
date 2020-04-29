<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

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

            return $this->redirectToRoute('game', ['url' => $url]);
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

        // BOF add pieces
        if ($playerCreatorColor === 'white') {
            $arrChar = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h'];
            foreach ($arrChar as $value) {
                $entityManager = $this->placePiece('pawn', $value.'2', $playerCreatorEntity, $entityManager);

            }

            $entityManager = $this->placePiece('rook', 'a1', $playerCreatorEntity, $entityManager);
            $entityManager = $this->placePiece('rook', 'h1', $playerCreatorEntity, $entityManager);
            $entityManager = $this->placePiece('knight', 'b1', $playerCreatorEntity, $entityManager);
            $entityManager = $this->placePiece('knight', 'g1', $playerCreatorEntity, $entityManager);
            $entityManager = $this->placePiece('bishop', 'c1', $playerCreatorEntity, $entityManager);
            $entityManager = $this->placePiece('bishop', 'f1', $playerCreatorEntity, $entityManager);
            $entityManager = $this->placePiece('queen', 'd1', $playerCreatorEntity, $entityManager);
            $entityManager = $this->placePiece('king', 'e1', $playerCreatorEntity, $entityManager);

            foreach ($arrChar as $value) {
                $entityManager = $this->placePiece('pawn', $value.'7', $playerGuestEntity, $entityManager);
            }

            $entityManager = $this->placePiece('rook', 'a8', $playerGuestEntity, $entityManager);
            $entityManager = $this->placePiece('rook', 'h8', $playerGuestEntity, $entityManager);
            $entityManager = $this->placePiece('knight', 'b8', $playerGuestEntity, $entityManager);
            $entityManager = $this->placePiece('knight', 'g8', $playerGuestEntity, $entityManager);
            $entityManager = $this->placePiece('bishop', 'c8', $playerGuestEntity, $entityManager);
            $entityManager = $this->placePiece('bishop', 'f8', $playerGuestEntity, $entityManager);
            $entityManager = $this->placePiece('queen', 'd8', $playerGuestEntity, $entityManager);
            $entityManager = $this->placePiece('king', 'e8', $playerGuestEntity, $entityManager);
        } else {
            $arrChar = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h'];
            foreach ($arrChar as $value) {
                $entityManager = $this->placePiece('pawn', $value.'2', $playerGuestEntity, $entityManager);
            }

            $entityManager = $this->placePiece('rook', 'a1', $playerGuestEntity, $entityManager);
            $entityManager = $this->placePiece('rook', 'h1', $playerGuestEntity, $entityManager);
            $entityManager = $this->placePiece('knight', 'b1', $playerGuestEntity, $entityManager);
            $entityManager = $this->placePiece('knight', 'g1', $playerGuestEntity, $entityManager);
            $entityManager = $this->placePiece('bishop', 'c1', $playerGuestEntity, $entityManager);
            $entityManager = $this->placePiece('bishop', 'f1', $playerGuestEntity, $entityManager);
            $entityManager = $this->placePiece('queen', 'd1', $playerGuestEntity, $entityManager);
            $entityManager = $this->placePiece('king', 'e1', $playerGuestEntity, $entityManager);

            foreach ($arrChar as $value) {
                $entityManager = $this->placePiece('pawn', $value.'7', $playerCreatorEntity, $entityManager);
            }

            $entityManager = $this->placePiece('rook', 'a8', $playerCreatorEntity, $entityManager);
            $entityManager = $this->placePiece('rook', 'h8', $playerCreatorEntity, $entityManager);
            $entityManager = $this->placePiece('knight', 'b8', $playerCreatorEntity, $entityManager);
            $entityManager = $this->placePiece('knight', 'g8', $playerCreatorEntity, $entityManager);
            $entityManager = $this->placePiece('bishop', 'c8', $playerCreatorEntity, $entityManager);
            $entityManager = $this->placePiece('bishop', 'f8', $playerCreatorEntity, $entityManager);
            $entityManager = $this->placePiece('queen', 'd8', $playerCreatorEntity, $entityManager);
            $entityManager = $this->placePiece('king', 'e8', $playerCreatorEntity, $entityManager);
        }
        // EOF add pieces

        // BOF update database
        $entityManager->flush();
        // EOF update database

        return;
    }

    private function placePiece(string $label, string $position, Entity\Player $playerEntity, $entityManager)
    {
        $pieceEntity = new Entity\Piece();
        $pieceEntity->setLabel($label);
        $pieceEntity->setPosition($position);
        $pieceEntity->setIsTaken(false);
        $pieceEntity->setPlayer($playerEntity);
        $entityManager->persist($pieceEntity);

        return $entityManager;
    }
}
