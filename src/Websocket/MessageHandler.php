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

    private $playerWhiteResourceId = [];
    private $playerBlackResourceId = [];

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
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        if ($this->debug) {
            print_r('onMessage'.PHP_EOL);
        }

        $msgArray = json_decode($msg, true);

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

        // We have a message to display in the tchat, save it in DB unless we have the 'noSave' element
        if (isset($msgArray['message']) && !empty($msgArray['message']) && !isset($msgArray['noSave'])) {
            $dateTimeNow = new \DateTime();

            $messagesEntity = new Entity\Messages();
            $messagesEntity->setDateInsert($dateTimeNow);
            // It's a message from the tchat, set the player
            if (isset($msgArray['isTchat'], $msgArray['color']) && !empty($msgArray['isTchat']) && !empty($msgArray['color'])) {
                if ($msgArray['color'] === 'w' || $msgArray['color'] === 'white') {
                    $messagesEntity->setPlayer($this->playerWhiteEntity[$idGame]);
                } elseif ($msgArray['color'] === 'b' || $msgArray['color'] === 'black') {
                    $messagesEntity->setPlayer($this->playerBlackEntity[$idGame]);
                }
            }
            $messagesEntity->setGame($this->gameEntity[$idGame]);
            $messagesEntity->setMessage($msgArray['message']);
            $this->em->persist($messagesEntity);
            $this->em->flush();
        }

        // One player resign, send info to the other
        if (isset($msgArray['method']) && $msgArray['method'] === 'resign') {
            $resignColor = null;
            foreach ($this->connections as $connection) {
                if ($connection !== $from) {
                    if ($connection->resourceId === $this->playerWhiteResourceId[$idGame]) {
                        $resignColor = 'w';
                        $connection->send($msg);
                    }

                    if ($connection->resourceId === $this->playerBlackResourceId[$idGame]) {
                        $resignColor = 'b';
                        $connection->send($msg);
                    }
                }
            }

            $this->gameEntity[$idGame]->setStatus('finished');
            $this->gameEntity[$idGame]->setWinner($resignColor);
            $this->gameEntity[$idGame]->setEndReason('resign');


            $this->em->persist($this->gameEntity[$idGame]);
            $this->em->flush();

            return;
        }

        // Send a draw offer to the opponent
        if (isset($msgArray['method']) && $msgArray['method'] === 'offer-draw') {
            foreach ($this->connections as $connection) {
                if ($connection !== $from && in_array($connection->resourceId, [$this->playerWhiteResourceId[$idGame], $this->playerBlackResourceId[$idGame]])) {
                    $connection->send($msg);
                }
            }

            return;
        }

        // Draw confirm, save the new game status
        if (isset($msgArray['method']) && $msgArray['method'] === 'offer-draw-yes') {
            foreach ($this->connections as $connection) {
                if ($connection !== $from && in_array($connection->resourceId, [$this->playerWhiteResourceId[$idGame], $this->playerBlackResourceId[$idGame]])) {
                    $connection->send($msg);
                }
            }

            $this->gameEntity[$idGame]->setStatus('finished');
            $this->gameEntity[$idGame]->setWinner('d'); // d for draw
            $this->gameEntity[$idGame]->setEndReason('playersAgreement');


            $this->em->persist($this->gameEntity[$idGame]);
            $this->em->flush();

            return;
        }

        // Draw reject, only send the message
        if (isset($msgArray['method']) && $msgArray['method'] === 'offer-draw-no') {
            foreach ($this->connections as $connection) {
                if ($connection !== $from && in_array($connection->resourceId, [$this->playerWhiteResourceId[$idGame], $this->playerBlackResourceId[$idGame]])) {
                    $connection->send($msg);
                }
            }

            return;
        }

        // Ask for a takeback to the opponent
        if (isset($msgArray['method']) && $msgArray['method'] === 'takeback') {
            foreach ($this->connections as $connection) {
                if ($connection !== $from && in_array($connection->resourceId, [$this->playerWhiteResourceId[$idGame], $this->playerBlackResourceId[$idGame]])) {
                    $connection->send($msg);
                }
            }

            return;
        }

        // Takeback accepted, save the new datas, remove the move
        if (isset($msgArray['method']) && $msgArray['method'] === 'takeback-yes') {
            foreach ($this->connections as $connection) {
                if ($connection !== $from && in_array($connection->resourceId, [$this->playerWhiteResourceId[$idGame], $this->playerBlackResourceId[$idGame]])) {
                    $connection->send($msg);
                }
            }

            $this->gameEntity[$idGame]->setFen($msgArray['fen']); // Save the new fen
            $this->gameEntity[$idGame]->setPgn($msgArray['pgn']); // Save the new pgn
            $this->em->persist($this->gameEntity[$idGame]);

            $moves = $this->em->getRepository(Entity\Moves::class)->findBy(
                ['game' => $idGame],
                ['id' => 'DESC']
            );

            $oneMore = true;
            foreach ($moves as $oneMove) {
                $this->em->remove($oneMove); // Remove one Move

                // If onlyOne true, break the loop to remove only one move
                if ($msgArray['onlyOne'] || !$oneMore) {
                    break;
                } else { // If not, go next to remove another one, but set the varaible to break after that
                    $oneMore = false;
                    continue;
                }
            }

            $this->em->flush();

            return;
        }

        // Takeback reject, only send the message
        if (isset($msgArray['method']) && $msgArray['method'] === 'takeback-no') {
            foreach ($this->connections as $connection) {
                if ($connection !== $from && in_array($connection->resourceId, [$this->playerWhiteResourceId[$idGame], $this->playerBlackResourceId[$idGame]])) {
                    $connection->send($msg);
                }
            }

            return;
        }

        // If it's a reconnection, send the timers
        if (isset($msgArray['method']) && $msgArray['method'] === 'connection') {
            dump($msgArray);
            if ($msgArray['color'] === 'w') {
                $this->playerWhiteResourceId[$idGame] = $from->resourceId;
            } else {
                $this->playerBlackResourceId[$idGame] = $from->resourceId;
            }

            $microtimeNow = microtime(true);
            foreach ($this->connections as $connection) {
                if (
                    (isset($this->playerWhiteResourceId[$idGame]) && $connection->resourceId === $this->playerWhiteResourceId[$idGame]) ||
                    (isset($this->playerBlackResourceId[$idGame]) && $connection->resourceId === $this->playerBlackResourceId[$idGame])
                ){
                    $theoricBlackTimeSpend = $this->blackMicrotimeSpend[$idGame] + ($microtimeNow - $this->blackMicrotimeStart[$idGame]);
                    $theoricWhiteTimeSpend = $this->whiteMicrotimeSpend[$idGame] + ($microtimeNow - $this->whiteMicrotimeStart[$idGame]);

                    // If game is finished, do not send the timer being decremented. Get the each players one saved in DB
                    if ($this->gameEntity[$idGame]->getStatus() === 'finished') {
                        $msg = json_encode([
                            'method' => 'opponent_connect',
                            'whiteMicrotimeSpend' => $this->playerWhiteEntity[$idGame]->getTimeLeft(),
                            'blackMicrotimeSpend' => $this->playerBlackEntity[$idGame]->getTimeLeft(),
                        ]);
                    } else {
                        $msg = json_encode([
                            'method' => 'opponent_connect',
                            'whiteMicrotimeSpend' => round($this->gameTimer[$idGame] - $theoricWhiteTimeSpend),
                            'blackMicrotimeSpend' => round($this->gameTimer[$idGame] - $theoricBlackTimeSpend),
                        ]);
                    }

                    $connection->send($msg);
                }
            }
            return;
        }

        // Game is timeout
        if (isset($msgArray['method']) && $msgArray['method'] === 'timeout') {
            $this->gameEntity[$idGame]->setStatus('finished');
            $this->gameEntity[$idGame]->setWinner($msgArray['color']);
            $this->gameEntity[$idGame]->setEndReason('timeout');

            $this->em->persist($this->gameEntity[$idGame]);
            $this->em->flush();

            foreach ($this->connections as $connection) {
                if ($connection !== $from && in_array($connection->resourceId, [$this->playerWhiteResourceId[$idGame], $this->playerBlackResourceId[$idGame]])) {
                    $connection->send($msg);
                }
            }

            return;
        }

        // At this point, we need to have a move method. If not, there is a problem ...
        if (!isset($msgArray['method']) || $msgArray['method'] !== 'move') {
            $from->close();
            return false;
        }

        // Game just finished because of a move, so don't return
        if (isset($msgArray['gameStatus'], $msgArray['gameReason'])) {
            $this->gameEntity[$idGame]->setStatus('finished');

            switch ($msgArray['gameStatus']) {
                case 'checkmate':
                    $this->gameEntity[$idGame]->setWinner($msgArray['color']);
                    $this->gameEntity[$idGame]->setEndReason($msgArray['gameReason']);
                    break;
                case 'draw':
                    $this->gameEntity[$idGame]->setWinner('d');
                    $this->gameEntity[$idGame]->setEndReason($msgArray['gameReason']);
                    break;
                default:
                    break;
            }

            $this->em->persist($this->gameEntity[$idGame]);
            $this->em->flush();
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
                if (!isset($this->playerWhiteResourceId[$idGame])) {
                    dump($this->playerWhiteResourceId);
                }
                if (!isset($this->playerBlackResourceId[$idGame])) {
                    dump($this->playerBlackResourceId);
                }
                if ($connection !== $from && ($connection->resourceId === $this->playerWhiteResourceId[$idGame] || $connection->resourceId === $this->playerBlackResourceId[$idGame])) {
                    $connection->send($msg);
                }
            }

            $this->gameEntity[$idGame]->setFen($msgArray['after']); // Save the new fen
            $this->gameEntity[$idGame]->setPgn($msgArray['pgn']); // Save the new pgn
            // Game still in begining status, update it
            if ($this->gameEntity[$idGame]->getStatus() === 'begining') {
                $this->gameEntity[$idGame]->setStatus('inplay');
            }
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
            if ($connection !== $conn && ($connection->resourceId === $this->playerWhiteResourceId[$idGame] || $connection->resourceId === $this->playerBlackResourceId[$idGame])) {
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