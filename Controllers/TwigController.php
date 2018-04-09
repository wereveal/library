<?php
/**
 * @brief     Admin for Twig config.
 * @ingroup   lib_controllers
 * @file      Ritc/Library/Controllers/TwigController.php
 * @namespace Ritc\Library\Controllers
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.0
 * @date      2017-05-14 14:36:29
 * @note Change Log
 * - v1.0.0-alpha.0 - Initial version        - 2017-05-14 wer
 * @todo Ritc/Library/Controllers/TwigController.php - Everything
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Interfaces\ControllerInterface;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ControllerTraits;
use Ritc\Library\Views\TwigView;

/**
 * Class TwigController.
 * @class   TwigController
 * @package Ritc\Library\Controllers
 */
class TwigController implements ControllerInterface
{
    use ControllerTraits;

    /**
     * TwigController constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupController($o_di);
    }

    /**
     * Routes things around to do the Twig management.
     * @return string
     */
    public function route()
    {
        $o_view = new TwigView($this->o_di);
        switch ($this->main_action) {
            default:
                return $o_view->render();
        }
    }
}