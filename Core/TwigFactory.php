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
 *  @version 0.2.0
 *  @date 2014-09-25 16:17:08
 *  @note A part of the RITC Library
 *  @note <pre><b>Change Log</b>
 *      v0.2.0 - changed the name of the method which is used to create/return the object
 *               and cleaned up some code.
 *      v0.1.1 - changed to implment the changes in Base class - 09/23/2014 wer
 *      v0.1.0 - initial file creation - 2013-11-11
 *  </pre>
**/
namespace Ritc\Library\Core;

use Twig_Loader_Filesystem;
use Twig_Environment;

class TwigFactory extends Base
{
    private $o_twig;
    private static $instance = array();
    protected $o_elog;
    protected $private_properties;

    private function __construct($a_twig_config)
    {
        $this->setPrivateProperties();
        $o_loader = new Twig_Loader_Filesystem($a_twig_config['default_path']);
        foreach ($a_twig_config['additional_paths'] as $path => $namespace ) {
            $o_loader->prependPath($path, $namespace);
        }
        $this->o_twig = new Twig_Environment($o_loader, $a_twig_config['environment_options']);
    }
    public static function create($config_file = 'twig_config.php')
    {
        list($name, $extension) = explode('.', $config_file);
        unset($extension);
        if (!isset(self::$instance[$name])) {
            $a_twig_config = $this->retrieveTwigConfigArray($config_file);
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
        return $this->o_twig;
    }
    private function retrieveTwigConfigArray($config_file)
    {
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
        return $a_twig_config;
    }
}
