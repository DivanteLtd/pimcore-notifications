<?php

namespace PimcoreNotifications;

use PimcoreNotifications\Console\RunCommand;
use Pimcore\API\Plugin as PluginLib;
use Pimcore\Console\Application;

/**
 * @author Kamil Karkus <kkarkus@divante.pl>
 */
final class Plugin extends PluginLib\AbstractPlugin implements PluginLib\PluginInterface
{
    public function init()
    {
        parent::init();

        if (!\Pimcore\Tool\Admin::isExtJS6()) {
            throw new \Exception('PimcoreNotifications plugin requires ExtJS6.');
        }

        \Pimcore::getEventManager()->attach(
            "system.console.init",
            function (\Zend_EventManager_Event $event) {
                /** @var Application $application */
                $application = $event->getTarget();
                $application->add(new RunCommand());
            }
        );
    }

    public static function install()
    {
        \Pimcore\Db::getConnection()
            ->query(file_get_contents(PIMCORE_PLUGINS_PATH . '/PimcoreNotifications/schema_up.sql'));
        return true;
    }
    
    public static function uninstall()
    {
        \Pimcore\Db::getConnection()
            ->query(file_get_contents(PIMCORE_PLUGINS_PATH . '/PimcoreNotifications/schema_down.sql'));
        return true;
    }

    public static function isInstalled()
    {
        $ret = false;
        try {
            $stmt = \Pimcore\Db::getConnection()->query('SHOW TABLES LIKE \'plugin_notifications\'');
            $ret = strcmp($stmt->fetch(\PDO::FETCH_COLUMN), "plugin_notifications") === 0;
        } catch (\Exception $e) {
            $ret = false;
        }
        return $ret;
    }

    public static function getTranslationFileDirectory()
    {
        return '/PimcoreNotifications/texts/';
    }

    public static function getTranslationFile($language)
    {
        return self::getTranslationFileDirectory() . $language . '.csv';
    }
}
