<?php

namespace Helper;

/**
 * Class Autoloader.
 */
class Autoloader
{

    /**
     * @return void
     */
    public static function register()
    {
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    /**
     * @param string $class
     *
     * @return void
     */
    public static function autoload($class)
    {
        require str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
    }
}
