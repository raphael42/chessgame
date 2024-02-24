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
    private $em;

    private $gameEntity = [];
    private $playerWhiteEntity = [];
    private $playerBlackEntity = [];

    private $gameTimer = [];
    private $gameIncrement = [];

    private $whiteMicrotimeStart = [];
    private $blackMicrotimeStart = [];

    private $whiteMicrotimeSpend = [];
    private $blackMicrotimeSpend = [];

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

        // $microtimeNow = microtime(true);
        // foreach ($this->connections as $connection) {
        //     $theoricBlackTimeSpend = $this->blackMicrotimeSpend + ($microtimeNow - $this->blackMicrotimeStart);
        //     $theoricWhiteTimeSpend = $this->whiteMicrotimeSpend + ($microtimeNow - $this->whiteMicrotimeStart);

        //     $msg = json_encode([
        //         'opponent_connect' => true,
        //         'whiteMicrotimeSpend' => round($this->gameTimer - $theoricWhiteTimeSpend),
        //         'blackMicrotimeSpend' => round($this->gameTimer - $theoricBlackTimeSpend),
        //     ]);
        //     $connection->send($msg);
        // }
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        if ($this->debug) {
            print_r('onMessage'.PHP_EOL);
        }

        $msgArray = json_decode($msg, true);

        // One player resign, send info to the other
        if (isset($msgArray['method']) && $msgArray['method'] === 'resign') {
            foreach ($this->connections as $connection) {
                if ($connection !== $from) {
                    $connection->send($msg);
                }
            }
        }

        // idGame missing, there is a problem ...
        if (!isset($msgArray['idGame'])) {
            $from->close();
            return false;
        }

        // Use this var to have cleaner code
        $idGame = $msgArray['idGame'];

        // Game not saved in the websocket yet, save it
        if (!isset($this->gameEntity[$idGame])) {
            // Get the game entity
            $this->gameEntity[$idGame] = $this->em->getRepository(Entity\Game::class)->find($idGame);

            // Get the game timer per player
            $this->gameTimer[$idGame] = $this->gameEntity[$idGame]->getTime();
            $this->gameIncrement[$idGame] = $this->gameEntity[$idGame]->getIncrement();

            // Get the players datas too
            $this->playerWhiteEntity[$idGame] = $this->em->getRepository(Entity\Player::class)->findOneBy([
                'color' => 'white',
                'game' => $this->gameEntity[$idGame]->getId(),
            ]);
            $this->playerBlackEntity[$idGame] = $this->em->getRepository(Entity\Player::class)->findOneBy([
                'color' => 'black',
                'game' => $this->gameEntity[$idGame]->getId(),
            ]);

            $this->whiteMicrotimeSpend[$idGame] = 0;
            $this->blackMicrotimeSpend[$idGame] = 0;

            $this->whiteMicrotimeStart[$idGame] = 0;
            $this->blackMicrotimeStart[$idGame] = 0;
        }

        // If it's a reconnection, send the timers
        if (isset($msgArray['method']) && $msgArray['method'] === 'connection') {
            $microtimeNow = microtime(true);
            foreach ($this->connections as $connection) {
                $theoricBlackTimeSpend = $this->blackMicrotimeSpend[$idGame] + ($microtimeNow - $this->blackMicrotimeStart[$idGame]);
                $theoricWhiteTimeSpend = $this->whiteMicrotimeSpend[$idGame] + ($microtimeNow - $this->whiteMicrotimeStart[$idGame]);

                $msg = json_encode([
                    'method' => 'opponent_connect',
                    'whiteMicrotimeSpend' => round($this->gameTimer[$idGame] - $theoricWhiteTimeSpend),
                    'blackMicrotimeSpend' => round($this->gameTimer[$idGame] - $theoricBlackTimeSpend),
                ]);
                $connection->send($msg);
            }
            return;
        }

        // At this point, we need to have a move method. If not, there is a problem ...
        if (!isset($msgArray['method']) || $msgArray['method'] !== 'move') {
            $from->close();
            return false;
        }


        $microtimeNow = microtime(true);
        if (isset($msgArray['after'])) {
            $msgArray['timer'] = round($this->gameTimer[$idGame]);
            $fenSplit = explode(' ', $msgArray['after']);
            // Second move and color black, start white timer
            if ((int) $fenSplit[5] === 2 && $msgArray['color'] === 'b') {
                $this->whiteMicrotimeStart[$idGame] = $microtimeNow;
            }

            // Timers has started and black played, update his timer
            if ((int) $fenSplit[5] > 2 && $msgArray['color'] === 'b') {
                $this->blackMicrotimeSpend[$idGame] += $microtimeNow - $this->blackMicrotimeStart[$idGame] - $this->gameIncrement[$idGame];
                $this->whiteMicrotimeStart[$idGame] = $microtimeNow;
                $msgArray['timer'] = round($this->gameTimer[$idGame] - $this->blackMicrotimeSpend[$idGame]);
            }

            // Timers has started and white played, update his timer
            if ((int) $fenSplit[5] >= 2 && $msgArray['color'] === 'w') {
                $this->whiteMicrotimeSpend[$idGame] += $microtimeNow - $this->whiteMicrotimeStart[$idGame] - $this->gameIncrement[$idGame];
                $this->blackMicrotimeStart[$idGame] = $microtimeNow;
                $msgArray['timer'] = round($this->gameTimer[$idGame] - $this->whiteMicrotimeSpend[$idGame]);
            }

            $msg = json_encode($msgArray);
            foreach ($this->connections as $connection) {
                if ($connection !== $from) {
                    $connection->send($msg);
                }
            }

            // Save the new fen
            $this->gameEntity[$idGame]->setFen($msgArray['after']);
            $this->em->persist($this->gameEntity[$idGame]);

            // Update player turn timer
            if ($msgArray['color'] === 'w') {
                $this->playerWhiteEntity[$idGame]->setTimeLeft($this->gameTimer[$idGame] - $this->whiteMicrotimeSpend[$idGame]);
                $this->em->persist($this->playerWhiteEntity[$idGame]);
            } else {
                $this->playerBlackEntity[$idGame]->setTimeLeft($this->gameTimer[$idGame] - $this->blackMicrotimeSpend[$idGame]);
                $this->em->persist($this->playerBlackEntity[$idGame]);
            }

            // Save the move
            $movesEntity = new Entity\Moves();
            if ($msgArray['color'] === 'w') {
                $movesEntity->setPlayer($this->playerWhiteEntity[$idGame]);
            } else {
                $movesEntity->setPlayer($this->playerBlackEntity[$idGame]);
            }
            $movesEntity->setGame($this->gameEntity[$idGame]);
            $movesEntity->setFenBefore($msgArray['before']);
            $movesEntity->setFenAfter($msgArray['after']);
            $movesEntity->setPiece($msgArray['piece']);
            $movesEntity->setSquareFrom($msgArray['from']);
            $movesEntity->setSquareTo($msgArray['to']);
            $movesEntity->setSan($msgArray['san']);
            $movesEntity->setLan($msgArray['lan']);
            $movesEntity->setFlags($msgArray['flags']);
            $movesEntity->setMoveNumber($msgArray['moveNumber']);
            if (isset($msgArray['promotion'])) {
                $movesEntity->setPromotion($msgArray['promotion']);
            }
            $this->em->persist($movesEntity);

            $this->em->flush();

            // echo 'this->gameIncrement[$idGame] : ';
            // var_dump($this->gameIncrement[$idGame]);
            // echo 'this->whiteMicrotimeSpend : ';
            // var_dump($this->whiteMicrotimeSpend[$idGame]);
            // echo 'this->blackMicrotimeSpend : ';
            // var_dump($this->blackMicrotimeSpend[$idGame]);

            // echo 'whiteMicrotimeSpend : ';
            // var_dump(gmdate("H:i:s", ($this->gameTimer[$idGame] - $this->whiteMicrotimeSpend[$idGame])));
            // // var_dump($this->whiteMicrotimeSpend[$idGame]);
            // echo PHP_EOL;
            // echo 'blackMicrotimeSpend : ';
            // var_dump(gmdate("H:i:s", ($this->gameTimer[$idGame] - $this->blackMicrotimeSpend[$idGame])));
            // // var_dump($this->blackMicrotimeSpend[$idGame]);
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
                    'method' => 'opponent_disconnect',
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