<?php
namespace Ritc\Library\Interfaces;

/**
 * Interface ViewInterface
 *
 * @package RITC_Library
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0
 * @date    2017-01-24 15:50:47
 * ## Change Log
 * - v1.0.0 - initial version                   - 2017-01-24 wer
 */
interface ViewInterface
{
    /**
     * Default method for rendering the html.
     * @return string
     */
    public function render();
}
