<?php
/**
 * @brief     Commonly used functions used in Manager Controllers.
 * @details   Commonly used functions used in Manager Controllers. Expands on Controller Traits.
 * @ingroup   lib_traits
 * @file      Ritc/Library/Traits/ConfigControllerTraits.php
 * @namespace Ritc\Library\Traits
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.2
 * @date      2017-07-04 14:28:39
 * @note Change Log
 * - v1.0.0-alpha.2 - Forked this into  two traits, this one dependent on the other       - 2017-07-04 wer
 *                    Provides unique capabilities for the Library controllers.
 * - v1.0.0-alpha.1 - Renamed Trait                                                       - 2017-06-20 wer
 * - v1.0.0-alpha.0 - Initial version                                                     - 2017-05-10 wer
 */
namespace Ritc\Library\Traits;

/**
 * Class ConfigControllerTraits.
 * @class   ConfigControllerTraits
 * @package Ritc\Library\Traits
 */
trait ConfigControllerTraits
{
    use ManagerControllerTraits;

    protected function testMethod()
    {
        return '';
    }
}
