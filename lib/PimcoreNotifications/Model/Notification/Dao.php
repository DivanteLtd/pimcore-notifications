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
        $data = $this->db->fetchRow("SELECT plugin_notifications.* FROM plugin_notifications WHERE plugin_notifications.id = ?", $id);

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
            $this->db->insert("plugin_notifications", [
                "title" => $this->model->getTitle(),
                "message" => $this->model->getMessage(),
                "type" => $this->model->getType(),
                "fromUser" => $this->model->getFromUser(),
                "user" => $this->model->getUser(),
                "unread" => $this->model->isUnread(),
                "linkedElement" => $this->model->getLinkedElement() ? $this->model->getLinkedElement()->getId() : null,
                "linkedElementType" => $this->model->getLinkedElementType(),
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
            $this->db->update("plugin_notifications", [
                "title" => $this->model->getTitle(),
                "message" => $this->model->getMessage(),
                "type" => $this->model->getType(),
                "fromUser" => $this->model->getFromUser(),
                "user" => $this->model->getUser(),
                "unread" => $this->model->isUnread(),
                "creationDate" => $this->model->getCreationDate(),
                "linkedElement" => $this->model->getLinkedElement() ? $this->model->getLinkedElement()->getId() : null,
                "linkedElementType" => $this->model->getLinkedElementType(),
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
            $this->db->delete("plugin_notifications", $this->db->quoteInto("id = ?", $this->model->getId()));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param array $data
     */
    protected function assignVariablesToModel($data)
    {
        parent::assignVariablesToModel($data);
        foreach ($data as $key => $value) {
            if ('linkedElement' == $key) {
                $type = $this->model->getLinkedElementType();
                if ('document' == $type) {
                    $this->model->setLinkedElement(\Pimcore\Model\Document::getById($value));
                } else if ('asset' == $type) {
                    $this->model->setLinkedElement(\Pimcore\Model\Asset::getById($value));
                } else if ('object' == $type) {
                    $this->model->setLinkedElement(\Pimcore\Model\Object::getById($value));
                }
            }
        }
    }
}
