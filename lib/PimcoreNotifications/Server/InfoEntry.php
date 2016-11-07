<?php

namespace PimcoreNotifications\Server;

use Pimcore\Model\User;

/**
 * Class InfoEntry
 *
 * @author Kamil Karkus <kkarkus@divante.pl>
 */
class InfoEntry
{
    /**
     * @var User
     */
    protected $user;
    /**
     * @var int
     */
    protected $unread;
    /**
     * @var array
     */
    protected $notifications;

    /**
     * InfoEntry constructor.
     *
     * @param User  $user
     * @param int   $unread
     * @param array $notifications
     */
    public function __construct(User $user, $unread, array $notifications)
    {
        $this->user = $user;
        $this->unread = $unread;
        $this->notifications = $notifications;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return InfoEntry
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return int
     */
    public function getUnread()
    {
        return $this->unread;
    }

    /**
     * @param int $unread
     *
     * @return InfoEntry
     */
    public function setUnread($unread)
    {
        $this->unread = $unread;
        return $this;
    }

    /**
     * @return array
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * @param array $notifications
     *
     * @return InfoEntry
     */
    public function setNotifications($notifications)
    {
        $this->notifications = $notifications;
        return $this;
    }
}