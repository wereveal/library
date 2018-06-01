<?php
/**
 * Class ContentController.
 * @package Ritc_Library
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Interfaces\ControllerInterface;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigControllerTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Manages the Content.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.0
 * @date    2018-05-30 16:58:04
 * @change_log
 * - v1.0.0-alpha.0 - Initial version        - 2018-05-30 wer
 * @todo ContentController.php - Everything
 */
class ContentController implements ControllerInterface
{
    use LogitTraits, ConfigControllerTraits;

    public function __construct(Di $o_di)
    {
        $this->setupManagerController($o_di);
        $this->setupElog($o_di);
    }

    public function route()
    {
        // TODO: Implement route() method.
    }
}
