<?php
/**
 * Class NavgroupsView.
 * @package Ritc_Library
 */
namespace Ritc\Library\Views;

use Ritc\Library\Interfaces\ViewInterface;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigViewTraits;

/**
 * View for the Navgroups Manager.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.0+
 * @date    2018-06-19 12:11:51
 * @change_log
 * - 1.0.0-alpha.0 - Initial version                            - 2018-06-19 wer
 * @todo NavgroupsView.php - Everything
 */
class NavgroupsView implements ViewInterface
{
    use ConfigViewTraits;

    /**
     * NavgroupsView constructor.
     *
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupView($o_di);
    }

    /**
     * Default method for rendering the html.
     *
     * @param array $a_message
     * @return string
     */
    public function render(array $a_message = []):string
    {
        return '';
    }

    /**
     * @param array $a_post
     * @return string
     */
    public function renderForm(array $a_post = [])
    {
        return '';
    }
}
