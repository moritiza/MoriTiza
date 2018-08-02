<?php

namespace Core;

class Loader
{
	private static $loaded = array();

    public static function load($object)
    {
        $valids = array(
            'Router',
            'Requests',
        );

        if (!in_array($object, $valids)) {
            throw new \Exception("Error: '($object)' Not Valid!", 1);

            return false;
        }

        if (!isset(self::$loaded[$object]) or empty(self::$loaded[$object])) {
            if (file_exists(__DIR__ . '/' . $object . '.php')) {
                $className = 'Core\\' . $object;
                self::$loaded[$object] = new $className();
            } elseif (file_exists(__DIR__ . '/Libraries/' . $object . '.php')) {
                $className = 'Core\Libraries\\' . $object;
                self::$loaded[$object] = new $className();
            }
        }

        return self::$loaded[$object];
    }
}