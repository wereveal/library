<?php
/**
 * Class LibSitemapController.
 * @package Ritc_Library
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ControllerInterface;
use Ritc\Library\Models\NavComplexModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigControllerTraits;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Views\LibSitemapView;

/**
 * Manager for Sitemap.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.0
 * @date    2018-05-27 18:46:57
 * @change_log
 * - v1.0.0-alpha.0 - Initial version        - 2018-05-27 wer
 * @todo LibSitemapController.php - Everything
 */
class LibSitemapController implements ControllerInterface
{
    use LogitTraits, ConfigControllerTraits;

    private $o_nav;
    private $o_view;

    public function __construct(Di $o_di)
    {
        $this->setupController($o_di);
        $this->o_view = new LibSitemapView($o_di);
        $this->o_nav  = new NavComplexModel($o_di);
        $this->setupElog($o_di);
    }

    public function route()
    {
        // TODO: Implement route() method.
        switch ($this->form_action) {
            case 'build_xml':
                $a_message = $this->o_view->createXmlSitemap();
                break;
            case 'add_to':
                $a_message = $this->addTo();
                break;
            case 'remove_from':
                $a_message = $this->removeFrom();
                break;
            default:
                $a_message = [];
        }
        return $this->o_view->render($a_message);
    }

    /**
     * Adds the navigation record to the Sitemap navgroup.
     * @return array
     */
    private function addTo()
    {
        $nav_id = $this->a_post['nav_id'];
        try {
            $results = $this->o_nav->addNavToSitemap($nav_id);
        }
        catch (ModelException $e) {
            return ViewHelper::failureMessage($e->getMessage());
        }
        return [];
    }

    private function removeFrom()
    {
        $nav_id = $this->a_post['nav_id'];
        try {
            $results = $this->o_nav->removeNavFromSitemap($nav_id);
        }
        catch (ModelException $e) {
            return ViewHelper::failureMessage($e->getMessage());
        }
        return [];
    }

}
