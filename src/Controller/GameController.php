<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\HttpFoundation\Request;

use App\Entity;

class GameController extends AbstractController
{
    public function gamefunction($url, Request $request, EntityManagerInterface $entityManager, #[CurrentUser] ?Entity\User $user)
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

                $flushNeeded = true;

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

                // First opponent connexion, fill the user id it's set. The game creator has his field filled at the creation
                $player->setUser($user);
                $entityManager->persist($player);

                $flushNeeded = true;
            } else { // Infos already saved in DB, check playerType session. If not exist or not the player color, it's a spectator
                if (empty($gameSession) || !isset($gameSession['id']) || is_null($gameSession['id']) || $gameSession['id'] !== $game->getId() || !isset($gameSession['playerType'])) { // No playerType session, it's a spectator
                    $gameSession['playerType'] = 'spectator';
                    $session->set('gameDatas', $gameSession);

                    // If spectator, the main player is white
                    $tmpPlayer = $player;
                    $player = $opponent;
                    $opponent = $tmpPlayer;
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
            if ($oneMove->getPlayer()->getColor() === 'w') {
                $arrMovesForHtml[$oneMove->getMoveNumber()]['san_white'] = $oneMove->getSan();
            }

            if ($oneMove->getPlayer()->getColor() === 'b') {
                $arrMovesForHtml[$oneMove->getMoveNumber()]['san_black'] = $oneMove->getSan();
            }
        }

        // Game status is waiting players and we get the ip of the two players, then set begining status
        $gameUnavailable = false; // Use to remove from homepage the game from waiting list
        if ($game->getStatus() === 'waiting-player' && $player->getIp() !== null && $opponent->getIp() !== null) {
            $game->setStatus('begining');
            $entityManager->persist($game);
            $flushNeeded = true;

            // Only if random. Later for ranked too
            if ($game->getType() === 'random') {
                $gameUnavailable = true;
            }
        }

        if ($flushNeeded) {
            $entityManager->flush();
        }

        return $this->render('game.html.twig', [
            'game' => $game,
            'player' => $player,
            'opponent' => $opponent,
            'arrMovesForHtml' => $arrMovesForHtml,
            'messages' => $messages,
            'playerType' => $playerType,
            'gameUnavailable' => $gameUnavailable,
        ]);
    }

    public function gameawaitfunction($url, EntityManagerInterface $entityManager)
    {
        $game = $entityManager->getRepository(Entity\Game::class)->findOneBy([
            'url' => $url,
        ]);

        $player1 = $entityManager->getRepository(Entity\Player::class)->findOneBy([
            'game_creator' => true,
            'game' => $game->getId(),
        ]);

        $player2 = $entityManager->getRepository(Entity\Player::class)->findOneBy([
            'game_creator' => false,
            'game' => $game->getId(),
        ]);

        // 2 players found, go to the game page
        if ($player1->getIp() !== null && $player2->getIp() !== null) {
            return $this->redirectToRoute('game', ['url' => $url]);
        }

        return $this->render('game-await.html.twig', [
            'url' => $url,
        ]);
    }
}
