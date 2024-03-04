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

        $playerType = 'spectator';

        $gameSession = $session->get('gameDatas');

        // No gameid in session, add it
        if (!isset($gameSession['id'])) {
            $gameSession['id'] = $idGame;
            $session->set('gameDatas', $gameSession);
        }

        // The current player is the game creator
        if (isset($gameSession['gameCreator']) && $gameSession['gameCreator'] === true) {
            $player = $entityManager->getRepository(Entity\Player::class)->findOneBy([
                'game_creator' => true,
                'game' => $game->getId(),
            ]);

            $opponent = $entityManager->getRepository(Entity\Player::class)->findOneBy([
                'game_creator' => false,
                'game' => $game->getId(),
            ]);

            $playerType = $player->getColor();

            // Player infos not save in DB, it's the first connexion of the game creator player. Save infos to DB, and save the playerType in session
            if ($player->getIp() === null) {
                $player->setIp($_SERVER['REMOTE_ADDR']);
                $player->setUserAgent($_SERVER['HTTP_USER_AGENT']);
                $entityManager->persist($player);
                $entityManager->flush();

                // Set the playerType session the value of the player color
                $gameSession['playerType'] = $playerType;
                $session->set('gameDatas', $gameSession);
            }
        } else { // The current player is not the game creator. It's even the opponent, or a spectator
            $player = $entityManager->getRepository(Entity\Player::class)->findOneBy([
                'game_creator' => false,
                'game' => $game->getId(),
            ]);

            $opponent = $entityManager->getRepository(Entity\Player::class)->findOneBy([
                'game_creator' => true,
                'game' => $game->getId(),
            ]);

            // Player infos not save in DB, it's the first connexion of the opponent of the game creator player. Save infos to DB, and save the playerType in session
            if ($player->getIp() === null) {
                $playerType = $player->getColor();
                $gameSession['playerType'] = $playerType;
                $session->set('gameDatas', $gameSession);

                $player->setIp($_SERVER['REMOTE_ADDR']);
                $player->setUserAgent($_SERVER['HTTP_USER_AGENT']);
                $entityManager->persist($player);
                $entityManager->flush();

            } else { // Infos already saved in DB, check playerType session. If not exist or not the player color, it's a spectator
                if (empty($gameSession) || !isset($gameSession['id']) || is_null($gameSession['id']) || $gameSession['id'] !== $game->getId() || !isset($gameSession['playerType'])) { // No playerType session, it's a spectator
                    $gameSession['playerType'] = 'spectator';
                    $session->set('gameDatas', $gameSession);
                } elseif (!empty($gameSession) && !is_null($gameSession['id']) && $gameSession['id'] === $game->getId() && $gameSession['playerType'] === $player->getColor()) { // Opponent of the game creator player is reconnecting
                    $playerType = $player->getColor();
                }
            }
        }

        $moves = $entityManager->getRepository(Entity\Moves::class)->findBy([
            'game' => $game->getId(),
        ]);

        $messages = $entityManager->getRepository(Entity\Messages::class)->findBy([
            'game' => $game->getId(),
        ]);

        $arrMovesForHtml = []; // array moves for html rebuilt
        foreach ($moves as $oneMove) {
            if ($oneMove->getPlayer()->getColor() === 'white') {
                $arrMovesForHtml[$oneMove->getMoveNumber()]['san_white'] = $oneMove->getSan();
            }

            if ($oneMove->getPlayer()->getColor() === 'black') {
                $arrMovesForHtml[$oneMove->getMoveNumber()]['san_black'] = $oneMove->getSan();
            }
        }

        return $this->render('secondpage.html.twig', [
            'game' => $game,
            'gameStatus' => $gameStatus,
            'player' => $player,
            'opponent' => $opponent,
            'arrMovesForHtml' => $arrMovesForHtml,
            'messages' => $messages,
            'playerType' => $playerType,
        ]);
    }
}
