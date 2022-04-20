<?php
/**
 * Class RoutesGroupView
 * @package Ritc_Library
 */
namespace Ritc\Library\Views;

use Ritc\Library\Interfaces\ViewInterface;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigViewTraits;

/**
 * View for Route Group mapping admin.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.0
 * @date    2017-05-14 16:38:08
 * @change_log
 * - v1.0.0-alpha.0 - Initial version        - 2017-05-14 wer
 * @todo Ritc/Library/Views/RoutesGroupView.php - Everything
 */
class RoutesGroupView implements ViewInterface
{
    use ConfigViewTraits;

    /**
     * RoutesGroupView constructor.
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupView($o_di);
    }

    /**
     * Main method to render the html.
     *
     * @return string
     */
    public function render():string
    {
        // TODO: Implement render() method.
        return '';
    }
}
