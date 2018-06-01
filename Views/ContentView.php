<?php
/**
 * Class ContentView.
 * @package Ritc_Library
 */

namespace Ritc\Library\Views;

use Ritc\Library\Interfaces\ViewInterface;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Traits\ViewTraits;

/**
 * Manager for Content View.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.0
 * @date    2018-06-01 11:44:46
 * @change_log
 * - v1.0.0-alpha.0 - Initial version.                                    - 2018-06-01 wer
 * @todo    ContentView.php - Everything
 */
class ContentView implements ViewInterface
{
    use LogitTraits, ViewTraits;

    /**
     * ContentView constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupView($o_di);
        $this->a_object_names = [];
        $this->setupElog($o_di);
    }

    /**
     * Main method required by interface
     * @param array $a_message
     * @return string
     */
    public function render(array $a_message = [])
    {
        // TODO: Implement render() method.
        return '';
    }
}
