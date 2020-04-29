<?php
namespace App\Websocket;

use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

use App\Entity;
use Doctrine\ORM\EntityManagerInterface;

class MessageHandler implements MessageComponentInterface
{
    protected $connections;
    private $debug = false;
    private $playerWhiteEntity;
    private $playerBlackEntity;
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        if ($this->debug) {
            print_r('construct');
        }
        $this->connections = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        if ($this->debug) {
            print_r('onOpen');
        }
        $this->connections->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        if ($this->debug) {
            print_r('onMessage');
        }
        foreach ($this->connections as $connection) {
            if ($connection === $from) {
                continue;
            }

            $connection->send($msg);

            $msgArray = json_decode($msg, true);
            if (isset($msgArray['idGame'])) { // start of the game : instanciate players entites
                $gameEntity = $this->em->getRepository(Entity\Game::class)->find($msgArray['idGame']);
                $this->playerWhiteEntity = $this->em->getRepository(Entity\Player::class)->findOneBy([
                    'color' => 'white',
                    'game' => $gameEntity->getId(),
                ]);
                $this->playerBlackEntity = $this->em->getRepository(Entity\Player::class)->findOneBy([
                    'color' => 'black',
                    'game' => $gameEntity->getId(),
                ]);
            } else { // next : moving piece
                $pieceEntity = $this->em->getRepository(Entity\Piece::class)->findOneBy([
                    'position' => $msgArray['from'],
                    'player' => ($msgArray['color'] === 'white') ? $this->playerWhiteEntity->getId() : $this->playerBlackEntity->getId(),
                ]);
                $pieceEntity->setPosition($msgArray['to']);
                $this->em->persist($pieceEntity);

                $pieceEntity = $this->em->getRepository(Entity\Piece::class)->findOneBy([
                    'position' => $msgArray['to'],
                    'player' => ($msgArray['color'] === 'white') ? $this->playerBlackEntity->getId() : $this->playerWhiteEntity->getId(),
                ]);

                if ($pieceEntity !== null) {
                    $pieceEntity->setPosition(null);
                    $pieceEntity->setIsTaken(true);
                    $this->em->persist($pieceEntity);
                }
                $this->em->flush();
            }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        if ($this->debug) {
            print_r('onClose');
        }
        $this->connections->detach($conn);
    }

    public function onError(ConnectionInterface $conn, Exception $e)
    {
        if ($this->debug) {
            print_r('onError');
        }
        $this->connections->detach($conn);
        $conn->close();
    }
}