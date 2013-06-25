<?php
/**
 *  For use when Wer\Framework is used standalone.
 *  required Composer to have been been installed in vendor 
 *  directory just outside of the site doc root.
**/
namespace Wer\Framework\Library;

class Autoloader
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require $_SERVER['DOCUMENT_ROOT'] . '/../vendor/composer/ClassLoader.php';
        }
    }

    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('Autoloader', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader();
        spl_autoload_unregister(array('Autoloader', 'loadClassLoader'));

        $namespaces = require $_SERVER['DOCUMENT_ROOT']
            . '/../src/Wer/Framework/Resources/config/autoload_namespaces.php';
        foreach ($namespaces as $namespace => $path) {
            $loader->add($namespace, $path);
        }

        $loader->register(true);

        return $loader;
    }
}
