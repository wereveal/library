<?php
/**
 * @brief     A Twig Factory.
 * @details   Lets us create a twig object, specific to a configuration
 *            allowing multiple twig objects to render the html
 * @ingroup   lib_factories
 * @file      Ritc/Library/FactoriesTwigFactory.php
 * @namespace Ritc\Library\Factories
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.2.0+1
 * @date      2017-02-15 15:25:55
 * @note <b>Change Log</b>
 * - v1.2.0   - changed to allow config file to include a path.                                       - 2017-02-09 wer
 * - v1.1.0   - added the ability to get the loader used to add additional twig namespaces            - 2017-02-08 wer
 * - v1.0.0   - not sure why this is beta. Removed Base abstract class                                - 09/01/2015 wer
 * - v1.0.0ß2 - moved to the Factories namespace
 * - v1.0.0ß1 - moved to the Services namespace                                                       - 11/15/2014 wer
 * - v0.2.0   - changed the name of the method which is used to create/return the object              - 09/25/2014 wer
 *              and cleaned up some code.
 * - v0.1.1   - changed to implment the changes in Base class                                         - 09/23/2014 wer
 * - v0.1.0   - initial file creation                                                                 - 2013-11-11 wer
 */
namespace Ritc\Library\Factories;

use Ritc\Library\Helper\LocateFile;
use Twig_Loader_Filesystem;
use Twig_Environment;

/**
 * Class TwigFactory
 * @class   TwigFactory
 * @package Ritc\Library\Factories
 */
class TwigFactory
{
    private $o_loader;
    /** @var Twig_Environment */
    private $o_twig;
    /** @var array */
    private static $instance = array();

    /**
     * TwigFactory constructor.
     * @param $a_twig_config
     */
    private function __construct($a_twig_config)
    {
        $o_loader = new Twig_Loader_Filesystem($a_twig_config['default_path']);
        $this->o_loader = $o_loader;
        foreach ($a_twig_config['additional_paths'] as $path => $namespace ) {
            try {
                $o_loader->prependPath($path, $namespace);
            }
            catch (\Twig_Error_Loader $e) {
                error_log("Twig Loader Error: " . $e->getRawMessage());
                die("Twig Loader Error: " . $e->getMessage());
            }

        }
        $this->o_twig = new Twig_Environment($o_loader, $a_twig_config['environment_options']);
    }

    /**
     * Creates the Twig_Environment object to be used to render pages.
     * @param string $config_file
     * @return mixed
     */
    public static function create($config_file = 'twig_config.php', $namespace = '')
    {
        $org_config_file = $config_file;
        if (strpos($config_file, '/') !== false) {
            $a_parts = explode('/', $config_file);
            $config_file = $a_parts[count($a_parts) - 1];
        }
        list($name, $extension) = explode('.', $config_file);
        // error_log("Name of config: " . $name);
        unset($extension);
        if (!isset(self::$instance[$name])) {
            $a_twig_config = self::retrieveTwigConfigArray($org_config_file, $namespace = '');
            // error_log("Twig Config: ". var_export($a_twig_config, true));
            self::$instance[$name] = new TwigFactory($a_twig_config);
        }
        return self::$instance[$name];
    }

    /**
     * Returns the loader for the twig environment instance.
     * @param string $config_file
     * @return mixed
     */
    public static function getLoader($config_file = 'twig_config.php')
    {
       list($name, $extension) = explode('.', $config_file);
       unset($extension);
       return self::$instance[$name]->o_loader;
    }

    /**
     * Returns the twig environment object which we use to do all the
     * template rendering.
     * @param string $config_file
     * @return Twig_Environment
     */
    public static function getTwig($config_file = 'twig_config.php')
    {
        $o_tf = self::create($config_file);
        return $o_tf->o_twig;
    }

    /**
     * Returns the array from the config file.
     * @param string $config_file Required Can be a file name or file with path.
     * @param string $namespace   Optional. If omitted, looks in the SRC_CONFIG_PATH.
     *                            Use namespace format e.g. My\Namespace.
     * @return array
     */
    private static function retrieveTwigConfigArray($config_file = '', $namespace = '')
    {
        if ($config_file == '') { return []; }
        if (strpos($config_file, '/') !== false) {
            $config_w_path = $config_file;
        }
        else {
            $config_w_path = LocateFile::getConfigWithPath($config_file, $namespace);
        }
        if ($config_w_path != '') {
            $a_twig_config = require $config_w_path;
        }
        else {
            $a_twig_config = [];
        }
        return $a_twig_config;
    }
}
