<?php

namespace Helper;

/**
 * Class Autoloader.
 */
class Autoloader
{
    /**
     */
    public static function register()
    {
        spl_autoload_register([__CLASS__, 'autoload']);
    }

    /**
     * @param string $class
     */
    public static function autoload($class)
    {
        require __DIR__.'/../'.str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
    }
}
