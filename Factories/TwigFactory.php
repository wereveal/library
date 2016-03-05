<?php
/**
 *  @brief     A Twig Factory.
 *  @details   Lets us create a twig object, specific to a configuration
 *             allowing multiple twig objects to render the html
 *  @ingroup   ritc_library lib_factories
 *  @file      TwigFactory.php
 *  @namespace Ritc\Library\Services
 *  @class     TwigFactory
 *  @author    William E Reveal <bill@revealitconsulting.com>
 *  @version   1.0.0
 *  @date      2015-09-01 07:31:00
 *  @note <pre><b>Change Log</b>
 *      v1.0.0   - not sure why this is beta. Removed Base abstract class                                - 09/01/2015 wer
 *      v1.0.0ß2 - moved to the Factories namespace
 *      v1.0.0ß1 - moved to the Services namespace                                                       - 11/15/2014 wer
 *      v0.2.0   - changed the name of the method which is used to create/return the object              - 09/25/2014 wer
 *                 and cleaned up some code.
 *      v0.1.1   - changed to implment the changes in Base class                                         - 09/23/2014 wer
 *      v0.1.0   - initial file creation                                                                 - 2013-11-11 wer
 *  </pre>
**/
namespace Ritc\Library\Factories;

use Twig_Loader_Filesystem;
use Twig_Environment;

class TwigFactory
{
    /**
     * @var Twig_Environment
     */
    private $o_twig;
    /**
     * @var array
     */
    private static $instance = array();

    /**
     * TwigFactory constructor.
     * @param $a_twig_config
     */
    private function __construct($a_twig_config)
    {
        $o_loader = new Twig_Loader_Filesystem($a_twig_config['default_path']);
        foreach ($a_twig_config['additional_paths'] as $path => $namespace ) {
            $o_loader->prependPath($path, $namespace);
        }
        $this->o_twig = new Twig_Environment($o_loader, $a_twig_config['environment_options']);
    }
    /**
     * Creates the Twig_Environment object to be used to render pages.
     * @param string $config_file
     * @return mixed
     */
    public static function create($config_file = 'twig_config.php')
    {
        list($name, $extension) = explode('.', $config_file);
        unset($extension);
        if (!isset(self::$instance[$name])) {
            $a_twig_config = self::retrieveTwigConfigArray($config_file);
            self::$instance[$name] = new TwigFactory($a_twig_config);
        }
        return self::$instance[$name];
    }
    /**
     *  Returns the twig environment object which we use to do all the
     *  template rendering.
     *  @param string $config_file
     *  @return Twig_Environment
     */
    public static function getTwig($config_file = 'twig_config.php')
    {
        $o_tf = self::create($config_file);
        return $o_tf->o_twig;
    }
    /**
     * @param $config_file
     * @return array|mixed
     */
    private static function retrieveTwigConfigArray($config_file)
    {
        $config_w_path = APP_PATH . '/config/' . $config_file;
        if (!file_exists($config_w_path)) {
            $config_w_path = SITE_PATH . '/config/' . $config_file;
        }
        if (!file_exists($config_w_path)) {
            $a_twig_config = array('default_path' => $_SERVER['DOCUMENT_ROOT'] . '/assets/templates/default');
        }
        else {
            /** @noinspection PhpIncludeInspection */
            $a_twig_config = require $config_w_path;
        }
        return $a_twig_config;
    }
}
