<?php
/**
 *  Interface for page controllers
 *  @file PageControllerInterface.php
 *  @ingroup ritc_library interfaces
 *  @namespace Ritc/Library/Interfaces
 *  @class PageControllerInterface
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0
 *  @date 2013-12-12 12:34:12
 *  @note A part of the RITC Library v5
 *  @note <pre><b>Change Log</b>
 *      v1.0 - Initial version 12/12/2013
 *  </pre>
**/
namespace Ritc\Library\Interfaces;

interface PageControllerInterface
{
    /**
     *  Main Router and Puker outer (more descriptive method name).
     *  Turns over the hard work to the specific controllers through the router.
     *  @param none
     *  @return string $html
    **/
    public function renderPage();
    /**
     *  Routes the code to the appropriate sub-controllers or methods and returns a string.
     *  As much as I have been looking at putting the actual route pairs somewhere else
     *  it feels like the routes are so specific to the specific controller, they might as well
     *  be in the controller. This should change if ever we get a blog thing working.
     *  @param array $a_actions optional, the actions derived from the URL/Form
     *  @param array $a_values optional, the values from a form
     *  @return string normally html to be displayed.
    **/
    public function router(array $a_actions, array $a_values = array());
}
