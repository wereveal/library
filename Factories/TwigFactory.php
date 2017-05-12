<?php
/**
 * @brief     A Twig Factory.
 * @details   Lets us create a twig object, specific to a configuration(s)
 *            allowing multiple twig objects to render the html
 * @ingroup   lib_factories
 * @file      Ritc/Library/Factories/TwigFactory.php
 * @namespace Ritc\Library\Factories
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   2.0.1
 * @date      2017-05-11 17:10:19
 * @note <b>Change Log</b>
 * - v2.0.1   - bug fix                                                                                 - 2017-05-11 wer
 * - v2.0.0   - added 2 new create methods, createMultiSource, createFromArray                          - 2017-03-13 wer
 *              self::getTwig() rewritten to used the new methods but has backwards
 *              compatibility with version 1.
 *              Deleted getLoader as it did not provide any usefulness.
 * - v1.2.0   - changed to allow config file to include a path.                                         - 2017-02-09 wer
 * - v1.1.0   - added the ability to get the loader used to add additional twig namespaces              - 2017-02-08 wer
 * - v1.0.0   - not sure why this is beta. Removed Base abstract class                                  - 09/01/2015 wer
 * - v1.0.0ß2 - moved to the Factories namespace
 * - v1.0.0ß1 - moved to the Services namespace                                                         - 11/15/2014 wer
 * - v0.2.0   - changed the name of the method which is used to create/return the object                - 09/25/2014 wer
 *              and cleaned up some code.
 * - v0.1.1   - changed to implment the changes in Base class                                           - 09/23/2014 wer
 * - v0.1.0   - initial file creation                                                                   - 2013-11-11 wer
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
     * Returns the twig environment object which we use to do all the template rendering.
     * @param string|array $config        Optional but highly recommended. \ref twigfactory
     * @param string       $name          Optional, defaults to main. Can be the name of the instance or a namespace.
     * @param bool         $use_main_twig Optional, defaults to true. Only used when self::createMultiSource is used.
     * @return Twig_Environment
     */
    public static function getTwig($config = 'twig_config.php', $name = 'main', $use_main_twig = true)
    {
        if (is_array($config)) {
            if (empty($config)) {
                $o_tf = self::create('twig_config.php', $name);
            }
            elseif (isset($config['default_path'])) {
                $o_tf = self::createWithArray($config, $name);
            }
            else {
                $o_tf = self::createMultiSource($config, $name, $use_main_twig);
            }
        }
        else {
            $o_tf = self::create($config, $name);
        }
        return $o_tf->o_twig;
    }

    /**
     * Creates an instance of the class.
     * Note that this method returns the factory instance and not the Twig_Environment object.
     * Use self::getTwig() to get the Twig_Environment object.
     * The instance name will be derived from the config file name.
     * If the file name is the default name, the instance name will be main.
     * @param string $config_file Optional, defaults to twig_config.php
     * @param string $namespace   Optional, defaults to no namespace (looks for the file in all the usual places).
     *                            If namespace is given, it looks for the file in the namespace.
     * @return mixed
     */
    protected static function create($config_file = 'twig_config.php', $namespace = '')
    {
        $org_config_file = $config_file;
        if (strpos($config_file, '/') !== false) {
            $a_parts = explode('/', $config_file);
            $config_file = $a_parts[count($a_parts) - 1];
        }
        list($name, $extension) = explode('.', $config_file);
        unset($extension);
        $name = str_replace('twig_config_', '', $name);
        if ($name == 'twig_config' || $name == '') {
            $name = 'main';
        }
        if (!isset(self::$instance[$name])) {
            $a_twig_config = self::retrieveTwigConfigArray($org_config_file, $namespace);
            self::$instance[$name] = new TwigFactory($a_twig_config);
        }
        return self::$instance[$name];
    }

    /**
     * Creates an instance of the class.
     * Note that this method returns the factory instance and not the Twig_Environment object.
     * Use self::getTwig() to get the Twig_Environment object.
     * @param array $a_config_files Optional but if omitted silly, because it will only return the main twig instance
     *                              [['name' => 'twig_config.php', 'namespace' => 'Ritc\MyApp']...]
     * @param string $name          Optional but recommended as it will only return the main twig environment if it has already been defined.
     * @param bool   $use_main_twig Optional, defaults to true. If set to false, will only use the config files specified.
     * @return mixed
     */
    protected static function createMultiSource(array $a_config_files = [], $name = 'main', $use_main_twig = true)
    {
        if (!isset(self::$instance[$name])) {
            if (empty($a_config_files)) {
                return self::create('twig_config.php');
            }
            if ($use_main_twig) {
                $a_twig_config = self::retrieveTwigConfigArray('twig_config.php');
            }
            else {
                $a_twig_config = self::retrieveTwigConfigArray($a_config_files[0]['name'], $a_config_files[0]['namespace']);
                unset($a_config_files[0]);
            }
            foreach ($a_config_files as $a_config_file) {
                $config_file = $a_config_file['name'];
                $namespace = !empty($a_config_file['namespace'])
                    ? $a_config_file['namespace']
                    : '';
                $a_twig_config_next = self::retrieveTwigConfigArray($config_file, $namespace);
                if (!empty($a_twig_config_next['additional_paths'])) {
                    $a_twig_config['additional_paths'] = array_merge(
                        $a_twig_config['additional_paths'],
                        $a_twig_config_next['additional_paths']
                    );
                }
            }
            self::$instance[$name] = new TwigFactory($a_twig_config);
        }
        return self::$instance[$name];
    }

    /**
     * Creates an instance of the class.
     * Note that this method returns the factory instance and not the Twig_Environment object.
     * Use self::getTwig() to get the Twig_Environment object.
     * @param array $a_twig_config Required. Array must have three key pairs,
     *                             default_path        => string
     *                             additional_paths    => array map of path => twig_name
     *                             environment_options => array map of twig options, have at least the
     *                                 cache, debug, and auto_reload options set.
     * @param string $name         Optional but recommended. Name to give the instance. Defaults to main.
     * @return mixed
     */
    protected static function createWithArray(array $a_twig_config = [], $name = 'main')
    {
        if (!isset(self::$instance[$name])) {
            if (   empty($a_twig_config)
                || empty($a_twig_config['default_path'])
                || empty($a_twig_config['additional_paths'])
                || empty($a_twig_config['environment_options'])
            ) {
                return self::create();
            }
            self::$instance[$name] = new TwigFactory($a_twig_config);
        }
        return self::$instance[$name];
    }

    /**
     * Returns the array from the config file.
     * @param string $config_file Required Can be a file name or file with path.
     * @param string $namespace   Optional. If omitted, looks in the SRC_CONFIG_PATH.
     *                            Use namespace format e.g. My\Namespace.
     * @return array
     */
    private static function retrieveTwigConfigArray($config_file = 'twig_config.php', $namespace = '')
    {
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
