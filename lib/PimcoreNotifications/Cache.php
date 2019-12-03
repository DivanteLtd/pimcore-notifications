<?php

namespace PimcoreNotifications;

/**
 * Class Cache
 *
 * @author Kamil Karkus <kkarkus@divante.pl>
 */
class Cache
{
    /**
     * @param string $key
     *
     * @return mixed
     */
    public static function load($key)
    {
        return \Pimcore\Cache::load('notifications_token_user_' . $key);
    }

    /**
     * @param string $key
     * @param mixed $data
     */
    public static function save($key, $data)
    {
        $key = 'notifications_token_user_' . $key;
        $tags = ['notifications'];
        \Pimcore\Cache::save($data, $key, $tags, null, 0, true);
    }
}
