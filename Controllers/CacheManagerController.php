<?php
/**
 * Class CacheManagerController.
 * @package Ritc_Library
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Interfaces\ControllerInterface;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigControllerTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Main router for Symfony based cache..
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.0
 * @date    2018-05-30 15:45:21
 * @change_log
 * - v1.0.0-alpha.0 - Initial version        - 2018-05-30 wer
 * @todo CacheManagerController.php - Everything
 */
class CacheManagerController implements ControllerInterface
{
    use LogitTraits, ConfigControllerTraits;

    public function __construct(Di $o_di)
    {
        $this->setupElog($o_di);
        $this->setupManagerController($o_di);
    }

    public function route()
    {
        // TODO: Implement route() method.
    }
}
