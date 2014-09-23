<?php
/**
 *  @brief Creates a Twig instance to render templates.
 *  @description Lets us create a twig object, specific to a configuration
 *      allowing multiple twig objects to render the html
 *  @file Tpl.php
 *  @ingroup ritc_library core
 *  @namespace Ritc/Library/Core
 *  @class Tpl
 *  @author William Reveal <bill@revealitconsulting.com>
 *  @version 0.1.1
 *  @date 2014-09-23 12:06:08
 *  @note A part of the RITC Library v5
 *  @note <pre><b>Change Log</b>
 *      v0.1.1 - changed to implment the changes in Base class - 09/23/2014 wer
 *      v0.1.0 - initial file creation - 2013-11-11
 *  </pre>
**/
namespace Ritc\Library\Core;

use Twig_Loader_Filesystem;
use Twig_Environment;

class Tpl extends Base
{
    protected $private_properties;
    protected $o_elog;
    private $a_twig_config;

    public function __construct($config_file = 'twig_config.php')
    {
        $this->setPrivateProperties();
        $this->a_twig_config = $this->createConfig($config_file);
    }
    private function createConfig($config_file = 'twig_config.php')
    {
        list($name, $extension) = explode('.', $config_file);
        unset($extension);
        $config_w_path = APP_PATH . '/config/' . $config_file;
        if (!file_exists($config_w_path)) {
            $config_w_path = SITE_PATH . '/config/' . $config_file;
        }
        $a_twig_config = !file_exists($config_w_path)
            ? array('default_path' => $_SERVER['DOCUMENT_ROOT'] . '/themes/default')
            : require $config_w_path;
        return $a_twig_config;
    }

    /**
     * Returns the twig environment object which we use to do all the
     * template rendering.
     * @return Twig_Environment
     */
    public function getTwig()
    {
        $o_loader = new Twig_Loader_Filesystem($this->a_twig_config['default_path']);
        foreach ($this->a_twig_config['additional_paths'] as $path => $namespace ) {
            $o_loader->prependPath($path, $namespace);
        }
        $o_twig = new Twig_Environment($o_loader, $this->a_twig_config['environment_options']);
        return $o_twig;
    }
}
