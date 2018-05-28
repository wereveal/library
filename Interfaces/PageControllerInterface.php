<?php
/**
 * Interface PageControllerInterface
 * @package Ritc_Library
 */
namespace Ritc\Library\Interfaces;

/**
 * Interface for page controllers.
 *
 * @author     William E Reveal <bill@revealitconsulting.com>
 * @version    v1.0.1
 * @date       2014-01-31 15:58:35
 * @deprecated v1.0.1 - controllers all route, not render, that is the views job.
 * @change_log
 * - v1.0.1 - fixed potential bug
 * - v1.0.0 - Initial version 12/12/2013
 */
interface PageControllerInterface
{
    /**
     * Main Router and Puker outer (more descriptive method name).
     * Turns over the hard work to the specific controllers through the router.
     * @return string $html
     */
    public function renderPage();
}
