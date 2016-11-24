<?php

use PimcoreNotifications\Model\Notification;

/**
 * Class PimcoreNotifications_IndexController
 *
 * @author Kamil Karkus <kkarkus@divante.pl>
 */
final class PimcoreNotifications_IndexController extends \Pimcore\Controller\Action\Admin
{
    /**
     * Regenerates token for currently logged user (websocket authorization)
     *
     * @return void
     */
    public function tokenAction()
    {
        $token = md5(time()) . '_' . mt_rand(1000000, 9999999);
        $userId = $this->getUser()->getId();
        $data = ['token' => $token, 'user' => $userId];
        $userIdHash = md5($userId);
        \PimcoreNotifications\Cache::save($userIdHash, $data);
        $this->_helper->json(['token' => $token, 'user' => $userIdHash]);
    }

    /**
     * Retrieves notifications json list for currently logged user.
     *
     * @throws Exception
     *
     * @return void
     */
    public function listAction()
    {
        $offset = $this->getParam("start") ? $this->getParam("start") : 0;
        $limit = $this->getParam("limit") ? $this->getParam("limit") : 40;

        $notifications = new Notification\Listing();
        $notifications
            ->setCondition('user = ?', $this->user->getId())
            ->setOffset($offset)
            ->setLimit($limit);
        $data = [];
        /** @var Notification $notification */
        foreach ($notifications->load() as $notification) {
            $date = new Zend_Date($notification->getCreationDate());
            $tmp = [
                'id' => $notification->getId(),
                'title' => $notification->getTitle(),
                'from' => '',
                'date' => $date->get('YYYY-MM-dd HH:mm:ss'),
                'unread' => $notification->isUnread(),
                'linkedElementType' => $notification->getLinkedElementType(),
                'linkedElementId' => null
            ];
            if ($notification->getLinkedElement()) {
                $tmp['linkedElementId'] = $notification->getLinkedElement()->getId();
            }
            /** @var \Pimcore\Model\User $fromUser */
            $fromUser = \Pimcore\Model\User::getById($notification->getFromUser());
            if ($fromUser) {
                $tmp['from'] = $fromUser->getFirstname() . ' ' . $fromUser->getLastname();
                if (0 === strlen(trim($tmp['from']))) {
                    $tmp['from'] = $fromUser->getName();
                }
            }
            $data[] = $tmp;
        }

        $this->_helper->json([
            "data" => $data,
            "success" => true,
            "total" => $notifications->getTotalCount(),
        ]);
    }

    /**
     * Retrieves unread new notifications & overall unread notifications for currently logged user.
     *
     * @return void
     */
    public function unreadAction()
    {
        $interval = $this->_getParam('interval', 10);
        $data = \PimcoreNotifications\Server\Helper::getUnread($this->user, time() - $interval);

        $this->_helper->json([
            "data" => $data,
            "success" => true,
            "total" => count($data),
            "unread" => \PimcoreNotifications\Server\Helper::getUnreadCount($this->user),
        ]);
    }

    /**
     * Retrieves json detailed notification data for a given id.
     *
     * @throws Exception
     *
     * @return void
     */
    public function detailsAction()
    {
        $id = (int)$this->getParam('id');
        $notification = Notification::getById($id);

        if (!$notification) {
            throw new Exception('Notification not found');
        }

        $date = new Zend_Date($notification->getCreationDate());
        $data = [
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
            $data['linkedElementId'] = $notification->getLinkedElement()->getId();
        }
        /** @var \Pimcore\Model\User $fromUser */
        $fromUser = \Pimcore\Model\User::getById($notification->getFromUser());
        if ($fromUser) {
            $data['from'] = $fromUser->getFirstname() . ' ' . $fromUser->getLastname();
            if (0 === strlen(trim($data['from']))) {
                $data['from'] = $fromUser->getName();
            }
        }

        $this->changeStatus($notification);
        $this->_helper->json([
            "data" => $data,
            "success" => true,
        ]);
    }

    /**
     * Delete notification for a given id.
     *
     * @throws Exception
     *
     * @return void
     */
    public function deleteAction()
    {
        $id = (int)$this->getParam('id');
        $notification = Notification::getById($id);

        if (!$notification) {
            throw new Exception('Notification not found');
        }

        $notification->delete();
        $this->_helper->json([
            "success" => true,
        ]);
    }

    /**
     * Deletes all notifications for currently logged user.
     *
     * @return void
     */
    public function deleteAllAction()
    {
        $notifications = new Notification\Listing();
        $notifications->setCondition('user = ?', [
            $this->user->getId(),
        ]);
        /** @var Notification $notification */
        foreach ($notifications->load() as $notification) {
            $notification->delete();
        }
        $this->_helper->json([
            "success" => true,
        ]);
    }

    /**
     * Marks a notification as read.
     *
     * @throws Exception
     *
     * @return void
     */
    public function markAsReadAction()
    {
        $id = (int)$this->getParam('id');
        $notification = Notification::getById($id);

        if (!$notification) {
            throw new Exception('Notification not found');
        }

        $this->changeStatus($notification);
        $this->_helper->json([
            "success" => true,
        ]);
    }

    /**
     * Marks a notification as read and saves an object.
     *
     * @param Notification $object
     *
     * @return void
     */
    private function changeStatus(Notification $object)
    {
        $object->setUnread(false);
        $object->save();
    }
}
