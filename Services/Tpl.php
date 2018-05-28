<?php
/**
 * Class Tpl
 * @package Ritc_Library
 */
namespace Ritc\Library\Services;

use Ritc\Library\Exceptions\FactoryException;
use Ritc\Library\Factories\TwigFactory;

/**
 * Class Tpl that is basically a stub for the TwigFactory.
 * Left for legacy.
 *
 * @deprecated v1.0.0
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0
 * @date    2015-09-03 14:19:00
 * @change_log
 * - v1.0.0   - took out of beta, removed abstract class Base                - 09/03/2015 wer
 * - v0.1.0ß1 - initial file creation                                        - 2013-11-11 wer
 */
class Tpl
{
    /**
     * Returns the twig environment object which we use to do all the template rendering.
     * @param string $config_file
     * @return \Twig_Environment|null
     */
    public function getTwig($config_file = 'twig_config.php')
    {
        try {
            return TwigFactory::getTwig($config_file);
        }
        catch(FactoryException $e) {
            return null;
        }
    }
}
