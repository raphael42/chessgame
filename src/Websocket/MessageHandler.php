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
    private $debug = true;
    private $playerWhiteEntity;
    private $playerBlackEntity;
    private $gameEntity;
    private $em;
    private $gameId;

    private $gameTimer;
    private $gameIncrement = 0;

    private $whiteMicrotimeStart;
    private $blackMicrotimeStart;

    private $whiteMicrotimeSpend = 0;
    private $blackMicrotimeSpend = 0;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        if ($this->debug) {
            print_r('construct'.PHP_EOL);
        }
        $this->connections = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        if ($this->debug) {
            print_r('onOpen'.PHP_EOL);
        }
        $this->connections->attach($conn);

        foreach ($this->connections as $connection) {
            $msg = json_encode([
                'opponent_connect' => true,
                'whiteMicrotimeSpend' => round($this->gameTimer - $this->whiteMicrotimeSpend),
                'blackMicrotimeSpend' => round($this->gameTimer - $this->blackMicrotimeSpend),
            ]);
            $connection->send($msg);
        }
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        if ($this->debug) {
            print_r('onMessage'.PHP_EOL);
        }

        $msgArray = json_decode($msg, true);
        // Game saved in class not the same as the one send in wbsocket, get the new one
        if (isset($msgArray['idGame'])) {
            if (is_null($this->gameEntity) || $this->gameEntity->getId() !== (int) $msgArray['idGame']) {
                // Get the game entity
                $this->gameEntity = $this->em->getRepository(Entity\Game::class)->find($msgArray['idGame']);

                // Get the game timer per player
                $this->gameTimer = $this->gameEntity->getTime();
                $this->gameIncrement = $this->gameEntity->getIncrement();

                // Get the players datas too
                $this->playerWhiteEntity = $this->em->getRepository(Entity\Player::class)->findOneBy([
                    'color' => 'white',
                    'game' => $this->gameEntity->getId(),
                ]);
                $this->playerBlackEntity = $this->em->getRepository(Entity\Player::class)->findOneBy([
                    'color' => 'black',
                    'game' => $this->gameEntity->getId(),
                ]);
            }
        }

        $microtimeNow = microtime(true);
        if (isset($msgArray['fen'])) {
            $msgArray['timer'] = round($this->gameTimer);
            $fenSplit = explode(' ', $msgArray['fen']);
            // Second move and color black, start white timer
            if ((int) $fenSplit[5] === 2 && $msgArray['color'] === 'black') {
                $this->whiteMicrotimeStart = $microtimeNow;
            }

            // Timers has started and black played, update his timer
            if ((int) $fenSplit[5] > 2 && $msgArray['color'] === 'black') {
                $this->blackMicrotimeSpend += $microtimeNow - $this->blackMicrotimeStart - $this->gameIncrement;
                $this->whiteMicrotimeStart = $microtimeNow;
                $msgArray['timer'] = round($this->gameTimer - $this->blackMicrotimeSpend);
            }

            // Timers has started and white played, update his timer
            if ((int) $fenSplit[5] >= 2 && $msgArray['color'] === 'white') {
                $this->whiteMicrotimeSpend += $microtimeNow - $this->whiteMicrotimeStart - $this->gameIncrement;
                $this->blackMicrotimeStart = $microtimeNow;
                $msgArray['timer'] = round($this->gameTimer - $this->whiteMicrotimeSpend);
            }

            $msg = json_encode($msgArray);
            foreach ($this->connections as $connection) {
                if ($connection !== $from) {
                    $connection->send($msg);
                }
            }

            // Save the new fen
            $this->gameEntity->setFen($msgArray['fen']);
            $this->em->persist($this->gameEntity);

            // Update player turn timer
            if ($msgArray['color'] === 'white') {
                $this->playerWhiteEntity->setTimeLeft($this->gameTimer - $this->whiteMicrotimeSpend);
                $this->em->persist($this->playerWhiteEntity);
            } else {
                $this->playerBlackEntity->setTimeLeft($this->gameTimer - $this->blackMicrotimeSpend);
                $this->em->persist($this->playerBlackEntity);
            }

            $this->em->flush();

            // echo 'this->gameIncrement : ';
            // var_dump($this->gameIncrement);
            // echo 'this->whiteMicrotimeSpend : ';
            // var_dump($this->whiteMicrotimeSpend);
            // echo 'this->blackMicrotimeSpend : ';
            // var_dump($this->blackMicrotimeSpend);

            // echo 'whiteMicrotimeSpend : ';
            // var_dump(gmdate("H:i:s", ($this->gameTimer - $this->whiteMicrotimeSpend)));
            // // var_dump($this->whiteMicrotimeSpend);
            // echo PHP_EOL;
            // echo 'blackMicrotimeSpend : ';
            // var_dump(gmdate("H:i:s", ($this->gameTimer - $this->blackMicrotimeSpend)));
            // // var_dump($this->blackMicrotimeSpend);
            // echo PHP_EOL;
            // echo PHP_EOL;
            // echo PHP_EOL;
            // echo PHP_EOL;
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        if ($this->debug) {
            print_r('onClose'.PHP_EOL);
        }
        foreach ($this->connections as $connection) {
            if ($connection !== $conn) {
                $msg = json_encode([
                    'opponent_disconnect' => true,
                ]);
                $connection->send($msg);
            }
        }

        $this->connections->detach($conn);
    }

    public function onError(ConnectionInterface $conn, Exception $e)
    {
        if ($this->debug) {
            print_r('onError'.PHP_EOL);
        }
        echo '<pre><br><br><br><br><br><br><br><br>';var_dump($e);echo '</pre>';
        $this->connections->detach($conn);
        $conn->close();
    }
}