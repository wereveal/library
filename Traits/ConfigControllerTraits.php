<?php
/**
 * Trait ConfigControllerTraits
 * @package Ritc_Library
 */
namespace Ritc\Library\Traits;

/**
 * Commonly used functions used in Library Controllers.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-alpha.2
 * @date    2017-07-04 14:28:39
 * @change_log
 * - v1.0.0-alpha.2 - Forked from ManagerControllerTraits.                      - 2017-07-04 wer
 *                    Provides unique capabilities for the Library controllers.
 * - v1.0.0-alpha.1 - Renamed Trait                                             - 2017-06-20 wer
 * - v1.0.0-alpha.0 - Initial version                                           - 2017-05-10 wer
 */
trait ConfigControllerTraits
{
    use ManagerControllerTraits;

    /**
     * @return string
     */
    protected function testMethod():string
    {
        return '';
    }
}
