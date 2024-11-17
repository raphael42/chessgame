<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity;

class EndGameController extends AbstractController
{
    public function ajaxRequestFunction(Request $request, EntityManagerInterface $entityManager): JsonResponse
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

        $game = $entityManager->getRepository(Entity\Game::class)->findOneBy([
            'url' => $data['game-url'],
        ]);

        if (is_null($game)) {
            // Return error
        }

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
}
