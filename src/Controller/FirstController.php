<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\Session;

use App\Entity;

class FirstController extends AbstractController
{
    public function firstfunction($url)
    {
        $game = $this->getDoctrine()->getRepository(Entity\Game::class)->findOneBy([
            'url' => $url,
        ]);

        $session = new Session();
        // $session->start();
        // $session->set('gameCreator', true); // TODELETE

        $player = $this->getDoctrine()->getRepository(Entity\Player::class)->findOneBy([
            'game_creator' => $session->get('gameCreator') ?? false,
            'game' => $game->getId(),
        ]);

        $colorPlayer = $player->getColor();

        if ($colorPlayer === 'white') {
            $whitePieces = $player->getPieces();
            $blackPlayer = $this->getDoctrine()->getRepository(Entity\Player::class)->findOneBy([
                'color' => 'black',
                'game' => $game->getId(),
            ]);
            $blackPieces = $blackPlayer->getPieces();
        } else {
            $blackPieces = $player->getPieces();
            $whitePlayer = $this->getDoctrine()->getRepository(Entity\Player::class)->findOneBy([
                'color' => 'white',
                'game' => $game->getId(),
            ]);
            $whitePieces = $whitePlayer->getPieces();
        }

        return $this->render('firstpage.html.twig', [
            'colorPlayer' => $colorPlayer,
            'whitePieces' => $whitePieces,
            'blackPieces' => $blackPieces,
        ]);
    }


    public function secondfunction($url)
    {
        $game = $this->getDoctrine()->getRepository(Entity\Game::class)->findOneBy([
            'url' => $url,
        ]);

        $idGame = $game->getId();

        $session = new Session();
        $session->start();
        // $session->set('gameCreator', true); // TODELETE

        $player = $this->getDoctrine()->getRepository(Entity\Player::class)->findOneBy([
            'game_creator' => $session->get('gameCreator') ?? false,
            'game' => $idGame,
        ]);

        $colorPlayer = $player->getColor();

        if ($colorPlayer === 'white') {
            $whitePieces = $player->getPieces();
            $blackPlayer = $this->getDoctrine()->getRepository(Entity\Player::class)->findOneBy([
                'color' => 'black',
                'game' => $idGame,
            ]);
            $blackPieces = $blackPlayer->getPieces();
        } else {
            $blackPieces = $player->getPieces();
            $whitePlayer = $this->getDoctrine()->getRepository(Entity\Player::class)->findOneBy([
                'color' => 'white',
                'game' => $idGame,
            ]);
            $whitePieces = $whitePlayer->getPieces();
        }

        return $this->render('secondpage.html.twig', [
            'idGame' => $idGame,
            'colorPlayer' => $colorPlayer,
            'whitePieces' => $whitePieces,
            'blackPieces' => $blackPieces,
            'fen' => $game->getFen(),
        ]);
    }
}
