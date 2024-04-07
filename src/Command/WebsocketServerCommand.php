<?php
namespace App\Command;

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

use App\Websocket\MessageHandler;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\ORM\EntityManagerInterface;
use React\EventLoop\Factory as LoopFactory;
use React\Socket\SecureServer;
use React\Socket\Server as ReactServer;

class WebsocketServerCommand extends Command
{
    protected static $defaultName = "run:websocket-server";

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $loop = LoopFactory::create();
        $webSock = new ReactServer('0.0.0.0:3001', $loop);

        $secureWebSock = new SecureServer($webSock, $loop, [
            'local_cert' => '/etc/letsencrypt/live/chess-league.com/cert.pem', // Chemin vers le certificat SSL
            'local_pk' => '/etc/letsencrypt/live/chess-league.com/privkey.pem',    // Chemin vers la clé privée
            'allow_self_signed' => false,             // À mettre à true seulement si vous utilisez un certificat auto-signé
            'verify_peer' => false                    // Peut être activé pour une vérification plus stricte
        ]);

        $server = new IoServer(
            new HttpServer(
                new WsServer(
                    new MessageHandler($this->em)
                )
            ),
            $secureWebSock,
            $loop
        );

        $output->writeln("Starting SSL server on port 3001");
        $server->run();
        return 0;
    }
}
