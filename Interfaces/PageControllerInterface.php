<?php
/**
 * @brief     Interface for page controllers
 * @details   Page Contollers are primary controllers, first thing called.
 *            Usually one Page Controller per App.
 * @ingroup   ritc_library lib_interfaces
 * @file      Ritc/Library/Interfaces/PageControllerInterface.php
 * @namespace Ritc\Library\Interfaces
 * @class     PageControllerInterface
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.1
 * @date      2014-01-31 15:58:35
 * @note <b>Change Log</b>
 * - v1.0.1 - fixed potential bug
 * - v1.0.0 - Initial version 12/12/2013
 */
namespace Ritc\Library\Interfaces;

interface PageControllerInterface
{
    /**
     * Main Router and Puker outer (more descriptive method name).
     * Turns over the hard work to the specific controllers through the router.
     * @param none
     * @return string $html
     */
    public function renderPage();
}
