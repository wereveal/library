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
 *  @version 0.2.0ß
 *  @date 2014-09-25 16:00:14
 *  @note A part of the RITC Library v5
 *  @note <pre><b>Change Log</b>
 *      v0.2.0ß - changed to use the TwigFactory - 09/25/2014 wer
 *                Hides the TwigFactory aspect, sort of, snicker. This is really a stupid class
 *      v0.1.1  - changed to implment the changes in Base class - 09/23/2014 wer
 *      v0.1.0  - initial file creation - 2013-11-11
 *  </pre>
**/
namespace Ritc\Library\Core;

use Ritc\Library\Abstracts\Base;

class Tpl extends Base
{
    protected $private_properties;
    protected $o_elog;
    private $o_twig_factory;

    public function __construct($config_file = 'twig_config.php')
    {
        $this->setPrivateProperties();
        $this->o_twig_factory = TwigFactory::create($config_file);
    }
    /**
     * Returns the twig environment object which we use to do all the template rendering.
     * @return Twig_Environment
     */
    public function getTwig()
    {
        return $this->o_twig_factory->getTwig();
    }
}
