<?php

namespace PimcoreNotifications\Model\Notification;

use PimcoreNotifications\Model\Notification;
use Pimcore\Model;

/**
 * @author Piotr Ćwięcek <pcwiecek@divante.pl>
 * @author Kamil Karkus <kkarkus@divante.pl>
 *
 * @property Notification $model
 */
class Dao extends Model\Dao\AbstractDao
{
    /**
     * Fetch a row by an id from the database and assign variables to the document model.
     *
     * @param $id
     *
     * @throws \Exception
     *
     * @return void
     */
    public function getById($id)
    {
        $data = $this->db->fetchRow("SELECT notifications.* FROM notifications WHERE notifications.id = ?", $id);

        if ($data["id"] > 0) {
            $this->assignVariablesToModel($data);
        } else {
            throw new \Exception("Notification with the ID " . $id . " doesn't exists");
        }
    }

    /**
     * @throws \Exception
     *
     * @return void
     */
    public function create()
    {
        try {
            $this->db->insert("notifications", [
                "title" => $this->model->getTitle(),
                "message" => $this->model->getMessage(),
                "type" => $this->model->getType(),
                "fromUser" => $this->model->getFromUser(),
                "user" => $this->model->getUser(),
                "unread" => $this->model->isUnread(),
                "creationDate" => time()
            ]);

            $this->model->setId($this->db->lastInsertId());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @throws \Exception
     *
     * @return void
     */
    public function update()
    {
        try {
            $this->db->update("notifications", [
                "title" => $this->model->getTitle(),
                "message" => $this->model->getMessage(),
                "type" => $this->model->getType(),
                "fromUser" => $this->model->getFromUser(),
                "user" => $this->model->getUser(),
                "unread" => $this->model->isUnread(),
                "creationDate" => $this->model->getCreationDate(),
                "modificationDate" => time()
            ], $this->db->quoteInto("id = ?", $this->model->getId()));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @throws \Exception
     *
     * @return void
     */
    public function save()
    {
        if ($this->model->getId()) {
            $this->update();
            return;
        }
        $this->create();
    }

    /**
     * Delete the row from the database. (based on the model id)
     *
     * @throws \Exception
     *
     * @return void
     */
    public function delete()
    {
        try {
            $this->db->delete("notifications", $this->db->quoteInto("id = ?", $this->model->getId()));
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
