<?php
/**
 * Class SitemapController.
 * @package Ritc_Library
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Interfaces\ControllerInterface;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ControllerTraits;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Views\SitemapView;

/**
 * Sitemap router.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.1.0
 * @date    2021-11-26 15:18:18
 * @change_log
 * - v1.1.0 - updated for php8                                  - 2021-11-26 wer
 * - v1.0.0 - Initial version                                   - 2018-05-27 wer
 */
class SitemapController implements ControllerInterface
{
    use LogitTraits;
    use ControllerTraits;

    /**
     * SitemapController constructor.
     *
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupController($o_di);
        $this->a_object_names = [];
        $this->setupElog($o_di);
    }

    /**
     * Main method to route to the appropriate controller/view/model
     *
     * @return string
     */
    public function route(): string
    {
        $o_view = new SitemapView($this->o_di);
        return $o_view->render();
    }
}
