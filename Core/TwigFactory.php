<?php
/**
 *  @brief A Twig Factory.
 *  @file TwigFactory.php
 *  @ingroup ritc_library core
 *  @namespace Ritc/Library/Core
 *  @class TwigFactory
 *  @author William Reveal <bill@revealitconsulting.com>
 *  @version 0.1.0
 *  @date 2013-11-11 12:49:59
 *  @note A part of the RITC Library v4
 *  @note <pre><b>Change Log</b>
 *      v0.1.0 - initial file creation - 2013-11-11
 *  </pre>
**/
namespace Ritc\Library\Core;

use Twig_Loader_Filesystem;
use Twig_Environment;

class TwigFactory extends Base
{
    private static $instance = array();
    private $o_elog;
    private $o_loader;
    protected $private_properties;

    private function __construct($a_twig_paths)
    {
        $this->setPrivateProperties();
        $this->o_elog = Elog::start();
        $o_loader = new Twig_Loader_Filesystem($a_twig_paths['default_path']);
        foreach ($a_twig_paths['additional_paths'] as $path => $namespace ) {
            $o_loader->prependPath($path, $namespace);
        }
        $this->o_loader = $o_loader;
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
            $o_twig_f = new TwigFactory($a_twig_config);
            $o_loader = $o_twig_f->getLoader();
            self::$instance[$name] = new Twig_Environment($o_loader, $a_twig_config['environment_options']);
        }
        return self::$instance[$name];
    }
    private function getLoader()
    {
        return $this->o_loader;
    }
}
