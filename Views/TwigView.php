<?php
/**
 * @brief     View for the Twig manager.
 * @details
 * @ingroup   lib_views
 * @file      Ritc/Library/Views/TwigView.php
 * @namespace Ritc\Library\Views
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.0
 * @date      2017-05-14 16:49:48
 * @note Change Log
 * - v1.0.0-alpha.0 - Initial version        - 2017-05-14 wer
 * @todo Ritc/Library/Views/TwigView.php - Everything
 */
namespace Ritc\Library\Views;

use Ritc\Library\Interfaces\ViewInterface;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Traits\ManagerViewTraits;

/**
 * Class TwigView.
 * @class   TwigView
 * @package Ritc\Library\Views
 */
class TwigView implements ViewInterface
{
    use ManagerViewTraits, LogitTraits;

    public function __construct(Di $o_di)
    {
        $this->setupElog($o_di);
        $this->setupView($o_di);
    }

    public function render()
    {
        // TODO: Implement render() method.
    }
}