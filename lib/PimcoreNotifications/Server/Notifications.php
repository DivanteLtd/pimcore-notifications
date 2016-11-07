<?php

namespace PimcoreNotifications\Server;

use PimcoreNotifications\Cache;
use Pimcore\Model\User;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

/**
 * Class Notifications
 *
 * @author Kamil Karkus <kkarkus@divante.pl>
 */
final class Notifications implements MessageComponentInterface
{

    /**
     * @var \SplObjectStorage|ConnectionInterface[]
     */
    protected $connections;

    /**
     * @var int
     */
    protected $lastPerodicTimer;

    /**
     * When a new connection is opened it will be passed to this method
     *
     * @param  ConnectionInterface $conn The socket/connection that just connected to your application
     *
     * @throws \Exception
     */
    public function onOpen(ConnectionInterface $conn)
    {
        if (!$this->connections) {
            $this->connections = new \SplObjectStorage();
        }

        $token = $conn->WebSocket->request->getQuery()['token'];
        $user = $conn->WebSocket->request->getQuery()['user'];

        if (!preg_match('@^[a-f0-9]+$@i', $user)) { // only md5
            $conn->close();
        }

        $cache = Cache::load($user);

        if (!$cache) {
            $conn->close();
        }

        if (0 !== strpos($cache['token'], $token)) { // not authorized
            $conn->close();
        }

        $this->connections->attach($conn, new InfoEntry(User::getById($cache['user']), 0, []));
    }

    /**
     * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not
     * result in an error if it has already been closed.
     *
     * @param  ConnectionInterface $conn The socket/connection that is closing/closed
     *
     * @throws \Exception
     */
    public function onClose(ConnectionInterface $conn)
    {
        $this->connections->detach($conn);
    }

    /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
     * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through
     * this method
     *
     * @param  ConnectionInterface $conn
     * @param  \Exception          $e
     *
     * @throws \Exception
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
    }

    /**
     * Triggered when a client sends data through the socket
     *
     * @param  \Ratchet\ConnectionInterface $from The socket/connection that sent the message to your application
     * @param  string                       $msg  The message received
     *
     * @throws \Exception
     */
    public function onMessage(ConnectionInterface $from, $msg)
    {
    }

    /**
     * Broadcast new notifications to all logged users.
     */
    public function onPeriodicTimer()
    {
        if (!$this->connections) {
            return;
        }

        if (!$this->lastPerodicTimer) {
            $this->lastPerodicTimer = time();
        }

        foreach ($this->connections as $conn) {
            /** @var InfoEntry $infoEntry */
            $infoEntry = $this->connections->getInfo();
            $user = $infoEntry->getUser();
            $data = [
                'unread' => Helper::getUnreadCount($user),
                'notifications' => Helper::getUnread($user, $this->lastPerodicTimer)
            ];
            $resent = false;
            $update = false;
            if ($infoEntry->getUnread() != $data['unread']) {
                $resent = true;
                $update = true;
                $infoEntry->setUnread($data['unread']);
            }
            if ($infoEntry->getNotifications() !== $data['notifications']) {
                if (count($data['notifications']) > 0) {
                    $resent = true;
                }
                $update = true;
                $infoEntry->setNotifications($data['notifications']);
            }
            if ($update) {
                $this->connections->attach($conn, $infoEntry);
            }
            if ($resent) {
                $conn->send(\Zend_Json::encode($data));
            }
        }

        $this->lastPerodicTimer = time();
    }
}