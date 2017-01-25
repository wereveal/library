<?php
/**
 * @brief     Interface used to set up view classes.
 * @ingroup   lib_interfaces
 * @file      Ritc/Library/Interfaces/ViewInterface.php
 * @namespace Ritc\Library\Interfaces
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.1
 * @date      2017-01-24 15:50:47
 * @note <b>Change Log</b>
 * - v1.0.0-alpha.1 - initial version                   - 2017-01-24 wer
 */
namespace Ritc\Library\Interfaces;

interface ViewInterface
{
    /**
     * Default method for rendering the html.
     * @return string
     */
    public function render();
}
