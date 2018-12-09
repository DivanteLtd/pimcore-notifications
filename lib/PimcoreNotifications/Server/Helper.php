<?php

namespace PimcoreNotifications\Server;

use PimcoreNotifications\Model\Notification;
use Pimcore\Model\User;
use Zend_Date;

/**
 * Class Helper
 *
 * @author Kamil Karkus <kkarkus@divante.pl>
 */
class Helper
{
    /**
     * Retrieves unread notifications json list for given user.
     *
     * @param User $user
     * @param int $lastRun
     * @return array
     * @throws \Zend_Date_Exception
     */
    public static function getUnread($user, $lastRun)
    {
        $notifications = new Notification\Listing();
        $notifications->setCondition('user = ? AND unread = 1 AND creationDate >= ?', [
            $user->getId(),
            $lastRun
        ]);
        $data = [];

        /** @var Notification $notification */
        foreach ($notifications->load() as $notification) {
            $date = new Zend_Date($notification->getCreationDate());
            $tmp = [
                'id' => $notification->getId(),
                'title' => $notification->getTitle(),
                'message' => $notification->getMessage(),
                'from' => '',
                'date' => $date->get('YYYY-MM-dd HH:mm:ss'),
                'type' => $notification->getType(),
                'linkedElementType' => $notification->getLinkedElementType(),
                'linkedElementId' => null
            ];
            if ($notification->getLinkedElement()) {
                $tmp['linkedElementId'] = $notification->getLinkedElement()->getId();
            }
            /** @var User $fromUser */
            $fromUser = User::getById($notification->getFromUser());
            if ($fromUser) {
                $tmp['from'] = $fromUser->getFirstname() . ' ' . $fromUser->getLastname();
                if (0 === strlen(trim($tmp['from']))) {
                    $tmp['from'] = $fromUser->getName();
                }
            }
            $data[] = $tmp;
        }

        return $data;
    }

    /**
     * Retrieves unread notifications count for given user.
     *
     * @param User $user
     *
     * @return int
     */
    public static function getUnreadCount($user)
    {
        $notifications = new Notification\Listing();
        $notifications->setCondition('user = ? AND unread = 1', $user->getId());

        return $notifications->getTotalCount();
    }
}
