<?php
/**
 * Interface ViewInterface
 * @package Ritc_Library
 */
namespace Ritc\Library\Interfaces;

/**
 * Interface to be used by views.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0
 * @date    2017-01-24 15:50:47
 * @change_log
 * - v1.0.0 - initial version                                   - 2017-01-24 wer
 */
interface ViewInterface
{
    /**
     * Default method for rendering the html.
     *
     * @return string
     */
    public function render():string ;
}
