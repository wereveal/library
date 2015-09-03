<?php
/**
 *  @brief Returns a Twig instance to render templates.
 *  @description Lets us create a twig object, specific to a configuration
 *      allowing multiple twig objects to render the html
 *  @file Tpl.php
 *  @ingroup ritc_library Services
 *  @namespace Ritc/Library/Services
 *  @class Tpl
 *  @author William Reveal <bill@revealitconsulting.com>
 *  @version 1.0.0ß
 *  @date 2014-12-05 14:19:00
 *  @note A part of the RITC Library v5
 *  @note <pre><b>Change Log</b>
 *      v1.0.0   - took out of beta, removed abstract class Base                - 09/03/2015 wer
 *      v1.0.0ß2 - changed to match TwigFactory - still a stupid class          - 12/05/2014 wer
 *                 Here to provide backward compatibility. From now on,
 *                 will just use the TwigFactory directly via DI. see setup.php
 *      v1.0.0ß1 - moved to Services namespace                                  - 11/15/2014 wer
 *      v0.2.0ß1 - changed to use the TwigFactory                               - 09/25/2014 wer
 *                 Hides the TwigFactory aspect, sort of, snicker.
 *                 This is really a stupid class
 *      v0.1.1ß1 - changed to implment the changes in Base class                - 09/23/2014 wer
 *      v0.1.0ß1 - initial file creation                                        - 2013-11-11 wer
 *  </pre>
**/
namespace Ritc\Library\Services;

use Ritc\Library\Factories\TwigFactory;
use Twig_Environment;

class Tpl
{

    /**
     * Returns the twig environment object which we use to do all the template rendering.
     * @return Twig_Environment
     */
    public function getTwig($config_file = 'twig_config.php')
    {
        return TwigFactory::getTwig($config_file);
    }
}
