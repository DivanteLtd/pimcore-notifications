<?php

namespace PimcoreNotifications\Console;

use PimcoreNotifications\Server\Notifications;
use Pimcore\Console\AbstractCommand;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RunCommand
 *
 * @author Kamil Karkus <kkarkus@divante.pl>
 */
final class RunCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('pimcore-notifications:run')
            ->setDescription('Starts WebSocket server for notifications.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $notifications = new Notifications();
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    $notifications
                )
            ),
            8080
        );

        $server->loop->addPeriodicTimer(5, [$notifications, 'onPeriodicTimer']);

        $server->run();
    }
}