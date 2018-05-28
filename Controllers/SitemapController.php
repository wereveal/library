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

/**
 * Sitemap router.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.0
 * @date    2018-05-27 18:46:57
 * @change_log
 * - v1.0.0-alpha.0 - Initial version        - 2018-05-27 wer
 * @todo SitemapController.php - Everything
 */
class SitemapController implements ControllerInterface
{
    use LogitTraits, ControllerTraits;

    public function __construct(Di $o_di)
    {
        $this->setupElog($o_di);
        $this->setupController($o_di);
    }

    /**
     * Main method to route to the appropriate controller/view/model
     * @return mixed
     */
    public function route()
    {

    }
}
