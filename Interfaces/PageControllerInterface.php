<?php
/**
 *  @brief Interface for page controllers
 *  @details Page Contollers are primary controllers, first thing called.
 *      Usually one Page Controller per App.
 *  @file PageControllerInterface.php
 *  @ingroup ritc_library interfaces
 *  @namespace Ritc/Library/Interfaces
 *  @class PageControllerInterface
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.1
 *  @date 2014-01-31 15:58:35
 *  @note A part of the RITC Library v5
 *  @note <pre><b>Change Log</b>
 *      v1.0.1 - fixed potential bug
 *      v1.0.0 - Initial version 12/12/2013
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
     *  be in the controller.
     *  @param array $a_actions optional, the actions derived from the URL/Form
     *  @param array $a_values optional, the values from a form
     *  @return string normally html to be displayed.
    **/
    public function router(array $a_actions = array(), array $a_values = array());
}
