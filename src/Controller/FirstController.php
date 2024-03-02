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
        $gameStatus = $game->getStatus();

        $session = new Session();
        $session->start();

        $player = $entityManager->getRepository(Entity\Player::class)->findOneBy([
            'game_creator' => (!is_null($session->get('gameCreator')) && $session->get('gameCreator') === $idGame) ? true : false,
            'game' => $idGame,
        ]);

        $opponent = $entityManager->getRepository(Entity\Player::class)->findOneBy([
            'game_creator' => (is_null($session->get('gameCreator')) || $session->get('gameCreator') !== $idGame) ? false : true,
            'game' => $idGame,
        ]);

        $moves = $entityManager->getRepository(Entity\Moves::class)->findBy([
            'game' => $idGame,
        ]);

        $messages = $entityManager->getRepository(Entity\Messages::class)->findBy([
            'game' => $idGame,
        ]);

        $arrMovesForJS = []; // array moves for JS file
        $arrMovesForHtml = []; // array moves for html rebuilt
        foreach ($moves as $oneMove) {
            $arrMovesForJS[] = [
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

            if ($oneMove->getPlayer()->getColor() === 'white') {
                $arrMovesForHtml[$oneMove->getMoveNumber()]['san_white'] = $oneMove->getSan();
            }

            if ($oneMove->getPlayer()->getColor() === 'black') {
                $arrMovesForHtml[$oneMove->getMoveNumber()]['san_black'] = $oneMove->getSan();
            }
        }

        return $this->render('secondpage.html.twig', [
            'idGame' => $idGame,
            'gameStatus' => $gameStatus,
            'player' => $player,
            'opponent' => $opponent,
            'arrMovesForJS' => $arrMovesForJS,
            'arrMovesForHtml' => $arrMovesForHtml,
            'messages' => $messages,
            'fen' => $game->getFen(),
            'increment' => $game->getIncrement(),
        ]);
    }
}
