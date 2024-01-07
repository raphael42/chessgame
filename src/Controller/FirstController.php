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

        return $this->render('secondpage.html.twig', [
            'idGame' => $idGame,
            'player' => $player,
            'opponent' => $opponent,
            'fen' => $game->getFen(),
            'increment' => $game->getIncrement(),
        ]);
    }
}
