<?php
/**
 *  @brief A Twig Factory.
 *  @description Lets us create a twig object, specific to a configuration
 *      allowing multiple twig objects to render the html
 *  @file TwigFactory.php
 *  @ingroup ritc_library core
 *  @namespace Ritc/Library/Core
 *  @class TwigFactory
 *  @author William Reveal <bill@revealitconsulting.com>
 *  @version 0.1.1
 *  @date 2014-09-23 12:07:08
 *  @note A part of the RITC Library
 *  @note <pre><b>Change Log</b>
 *      v0.1.1 - changed to implment the changes in Base class - 09/23/2014 wer
 *      v0.1.0 - initial file creation - 2013-11-11
 *  </pre>
**/
namespace Ritc\Library\Core;

use Twig_Loader_Filesystem;
use Twig_Environment;

class TwigFactory extends Base
{
    private static $instance = array();
    private static $a_twig_config;
    private static $o_twig_environment;
    protected $o_elog;
    protected $private_properties;

    private function __construct($a_twig_paths)
    {
        $this->setPrivateProperties();
        $o_loader = new Twig_Loader_Filesystem($a_twig_paths['default_path']);
        foreach ($a_twig_paths['additional_paths'] as $path => $namespace ) {
            $o_loader->prependPath($path, $namespace);
        }
        self::$o_twig_environment = new Twig_Environment($o_loader, self::$a_twig_config['environment_options']);
    }
    public static function start($config_file = 'twig_config.php')
    {
        list($name, $extension) = explode('.', $config_file);
        unset($extension);
        if (!isset(self::$instance[$name])) {
            $config_w_path = APP_PATH . '/config/' . $config_file;
            if (!file_exists($config_w_path)) {
                $config_w_path = SITE_PATH . '/config/' . $config_file;
            }
            if (!file_exists($config_w_path)) {
                $a_twig_config = array('default_path' => $_SERVER['DOCUMENT_ROOT'] . '/themes/default');
            }
            else {
                $a_twig_config = require $config_w_path;
            }
            self::$a_twig_config = $a_twig_config;
            self::$instance[$name] = new TwigFactory($a_twig_config);
        }
        return self::$instance[$name];
    }

    /**
     * Returns the twig environment object which we use to do all the
     * template rendering.
     * @return Twig_Environment
     */
    public function getTwig()
    {
        return self::$o_twig_environment;
    }

    /**
     * the properly named method for returning the class property
     * but I expect the getTwig method to be used mostly.
     * @return Twig_Environment
     */
    public function getTwigEnvironment()
    {
        return self::$o_twig_environment;
    }
    public function setTwigEnvironment()
    {
        // not allowed to set, just have the method here as placeholder
        return null;
    }
}
