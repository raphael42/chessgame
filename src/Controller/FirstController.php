<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity;

class FirstController extends AbstractController
{
    public function secondfunction($url, EntityManagerInterface $entityManager)
    {
        $game = $entityManager->getRepository(Entity\Game::class)->findOneBy([
            'url' => $url,
        ]);

        $idGame = $game->getId();

        $session = new Session();
        $session->start();
        // $session->set('gameCreator', true); // TODELETE

        $player = $entityManager->getRepository(Entity\Player::class)->findOneBy([
            'game_creator' => $session->get('gameCreator') ?? false,
            'game' => $idGame,
        ]);

        $opponent = $entityManager->getRepository(Entity\Player::class)->findOneBy([
            'game_creator' => !$session->get('gameCreator') ?? true,
            'game' => $idGame,
        ]);

        $moves = $entityManager->getRepository(Entity\Moves::class)->findBy([
            'game' => $idGame,
        ]);

        $arrMoves = [];
        foreach ($moves as $oneMove) {
            $arrMoves[] = [
                'fen_before' => $oneMove->getFenBefore(),
                'fen_after' => $oneMove->getFenAfter(),
                'piece' => $oneMove->getPiece(),
                'square_from' => $oneMove->getSquareFrom(),
                'square_to' => $oneMove->getSquareTo(),
                'san' => $oneMove->getSan(),
                'lan' => $oneMove->getLan(),
                'flags' => $oneMove->getFlags(),
                'promotion' => $oneMove->getPromotion(),
                'move_number' => $oneMove->getMoveNumber(),
                'player_color' => $oneMove->getPlayer()->getColor(),
                'player_id' => $oneMove->getPlayer()->getId(),
            ];
        }

        return $this->render('secondpage.html.twig', [
            'idGame' => $idGame,
            'player' => $player,
            'opponent' => $opponent,
            'arrMoves' => $arrMoves,
            'fen' => $game->getFen(),
            'increment' => $game->getIncrement(),
        ]);
    }
}
