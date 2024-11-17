<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Entity;

class GameAIController extends AbstractController
{
    public function gameaifunction($url, Request $request, EntityManagerInterface $entityManager)
    {
        $game = $entityManager->getRepository(Entity\Game::class)->findOneBy([
            'url' => $url,
        ]);

        $idGame = $game->getId();

        $session = $request->getSession();

        $playerType = 'spectator';

        $gameSession = $session->get('gameDatas');

        // No gameid in session, add a new gameDatas array in session
        if (!isset($gameSession['id']) || $gameSession['id'] !== $idGame) {
            $gameSession = [
                'id' => $idGame,
            ];
            $session->set('gameDatas', $gameSession);
        }

        // Use a variable to flush only one time
        $flushNeeded = false;

        $player = $entityManager->getRepository(Entity\Player::class)->findOneBy([
            'game_creator' => true,
            'game' => $game->getId(),
        ]);

        $opponent = $entityManager->getRepository(Entity\Player::class)->findOneBy([
            'game_creator' => false,
            'game' => $game->getId(),
        ]);

        // The current player is the game creator
        if (isset($gameSession['gameCreator']) && $gameSession['gameCreator'] === true) {
            $playerType = $player->getColor();

            // Player infos not save in DB, it's the first connexion of the game creator player. Save infos to DB, and save the playerType in session
            if ($player->getIp() === null) {
                $player->setIp($_SERVER['REMOTE_ADDR']);
                $player->setUserAgent($_SERVER['HTTP_USER_AGENT']);
                $entityManager->persist($player);

                $flushNeeded = true;

                // Set the playerType session the value of the player color
                $gameSession['playerType'] = $playerType;
                $session->set('gameDatas', $gameSession);
            }

            // Opponent is AI, set the server ip in opponent IP
            if ($opponent->getIp() === null) {
                $opponent->setIp($_SERVER['SERVER_ADDR'] ?? '127.0.0.1');
                $opponent->setUserAgent($_SERVER['HTTP_USER_AGENT']);

                // AI user
                $aiUser = $entityManager->getRepository(Entity\User::class)->findOneBy([
                    'email' => 'ai@chess-league.com',
                ]);
                $opponent->setUser($aiUser);

                $entityManager->persist($opponent);

                $flushNeeded = true;
            }
        } else { // The current player is not the game creator. It's a spectator. Do it later
            // Player infos not save in DB, it's the first connexion of the opponent of the game creator player. Save infos to DB, and save the playerType in session
            // if ($player->getIp() === null) {
            //     $playerType = $player->getColor();
            //     $gameSession['playerType'] = $playerType;
            //     $session->set('gameDatas', $gameSession);

            //     $player->setIp($_SERVER['SERVER_ADDR']);
            //     $player->setUserAgent($_SERVER['HTTP_USER_AGENT']);
            //     $entityManager->persist($player);

            //     $flushNeeded = true;
            // } else { // Infos already saved in DB, check playerType session. If not exist or not the player color, it's a spectator
            //     if (empty($gameSession) || !isset($gameSession['id']) || is_null($gameSession['id']) || $gameSession['id'] !== $game->getId() || !isset($gameSession['playerType'])) { // No playerType session, it's a spectator
            //         $gameSession['playerType'] = 'spectator';
            //         $session->set('gameDatas', $gameSession);

            //         // If spectator, the main player is white
            //         $tmpPlayer = $player;
            //         $player = $opponent;
            //         $opponent = $tmpPlayer;
            //     } elseif (!empty($gameSession) && !is_null($gameSession['id']) && $gameSession['id'] === $game->getId() && $gameSession['playerType'] === $player->getColor()) { // Opponent of the game creator player is reconnecting
            //         $playerType = $player->getColor();
            //     }
            // }
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

        // Game status is waiting players and we get the ip of the two players, then set begining status
        if ($game->getStatus() === 'waiting-player' && $player->getIp() !== null && $opponent->getIp() !== null) {
            $game->setStatus('begining');
            $entityManager->persist($game);
            $flushNeeded = true;
        }

        if ($flushNeeded) {
            $entityManager->flush();
        }

        return $this->render('game-ai.html.twig', [
            'game' => $game,
            'player' => $player,
            'opponent' => $opponent,
            'arrMovesForHtml' => $arrMovesForHtml,
            'messages' => $messages,
            'playerType' => $playerType,
        ]);
    }

    public function ajaxEndGameFunction(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $content = $request->getContent();
        $data = json_decode($content, true);

        if (!isset($data['game-url'])) {
            // Return error
        }

        if (!isset($data['winner-color'])) {
            // Return error
        }

        if (!isset($data['reason'])) {
            // Return error
        }

        if (!isset($data['fen'])) {
            // Return error
        }

        if (!isset($data['pgn'])) {
            // Return error
        }

        $game = $entityManager->getRepository(Entity\Game::class)->findOneBy([
            'url' => $data['game-url'],
        ]);

        if (is_null($game)) {
            // Return error
        }

        $game->setFen($data['fen']);
        $game->setPgn($data['pgn']);
        $game->setStatus('finished');
        $game->setWinner($data['winner-color']);
        $game->setEndReason($data['reason']);

        $entityManager->persist($game);
        $entityManager->flush();

        $response = [
            'success' => true,
        ];

        return new JsonResponse($response);
    }

    public function ajaxOneMoveFunction(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $content = $request->getContent();
        $data = json_decode($content, true);

        if (!isset($data['game-url'])) {
            // Return error
        }

        if (!isset($data['fen'])) {
            // Return error
        }

        if (!isset($data['pgn'])) {
            // Return error
        }

        $game = $entityManager->getRepository(Entity\Game::class)->findOneBy([
            'url' => $data['game-url'],
        ]);

        if (is_null($game)) {
            // Return error
        }

        $game->setFen($data['fen']);
        $game->setPgn($data['pgn']);

        $entityManager->persist($game);
        $entityManager->flush();

        $response = [
            'success' => true,
        ];

        return new JsonResponse($response);
    }
}
