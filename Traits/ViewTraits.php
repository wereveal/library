<?php
/**
 *  @brief     Common functions for views.
 *  @ingroup   ritc_library lib_traits
 *  @file      ViewTraits.php
 *  @namespace Ritc\Library\Traits
 *  @class     ViewTraits
 *  @author    William E Reveal <bill@revealitconsulting.com>
 *  @version   1.0.0-alpha.1
 *  @date      2016-03-10 14:18:23
 *  @note <pre><b>Change Log</b>
 *      v1.0.0-alpha.1 - close to working version                   - 2016-03-10 wer
 *      v1.0.0-alpha.0 - inital version                             - 2016-02-22 wer
 *  </pre>
 */
namespace Ritc\Library\Traits;

use Ritc\Library\Helper\AuthHelper;
use Ritc\Library\Helper\RoutesHelper;
use Ritc\Library\Models\NavComplexModel;
use Ritc\Library\Models\NavgroupsModel;
use Ritc\Library\Models\PageModel;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Services\Router;

trait ViewTraits
{
    /**
     * @var array
     */
    protected $a_nav;
    /**
     * @var int
     */
    protected $adm_level;
    /**
     * @var AuthHelper
     */
    protected $o_auth;
    /**
     * @var DbModel
     */
    protected $o_db;
    /**
     * @var Di
     */
    protected $o_di;
    /**
     * @var NavComplexModel
     */
    protected $o_nav;
    /**
     * @var PageModel
     */
    protected $o_page_model;
    /**
     * @var Router
     */
    protected $o_router;
    /**
     * @var \Ritc\Library\Helper\RoutesHelper
     */
    protected $o_routes_helper;
    /**
     * @var \Twig_Environment
     */
    protected $o_twig;

    /**
     *  The default setup for a view.
     *  @param Di $o_di
     */
    public function setupView(Di $o_di)
    {
        $this->setObjects($o_di);
        $this->setAdmLevel();
        $this->setNav();
    }

    /**
     * Sets the standard used objects from the object injector.
     * @param Di $o_di
     */
    protected function setObjects(Di $o_di)
    {
        $this->o_di            = $o_di;
        $this->o_router        = $o_di->get('router');
        $this->o_twig          = $o_di->get('twig');
        $this->o_db            = $o_di->get('db');
        $this->o_auth          = new AuthHelper($o_di);
        $this->o_page_model    = new PageModel($this->o_db);
        $this->o_nav           = new NavComplexModel($this->o_db);
        $this->o_routes_helper = new RoutesHelper($o_di);
    }

    /**
     *  Sets the class property $adm_level to a value of the highest auth level
     *  found or 0 if not found.
     *  @param string $login_id
     */
    protected function setAdmLevel($login_id = '')
    {
        if ($login_id != '') {
            $this->adm_level = $this->o_auth->getHighestAuthLevel($login_id);
        }
        elseif (isset($_SESSION['login_id'])) {
            $this->adm_level = $this->o_auth->getHighestAuthLevel($_SESSION['login_id']);
        }
        else {
            $this->adm_level = 0;
        }
    }

    /**
     * Sets the class property a_nav.
     * Uses the createNav method to do so.
     * @param int $nav_group optional, defaults to 1
     * @return null
     */
    protected function setNav($nav_group = 1)
    {
        if ($this->adm_level == '') {
            $this->setAdmLevel();
        }
        $a_nav = $this->o_nav->createNavArray($nav_group);
        $a_nav = $this->removeUnauthorizedLinks($a_nav);
        $a_nav = $this->specifyActiveMenu($a_nav);
        $this->a_nav = $a_nav;
    }

    /**
     * Sets the property a_nav array by the navgroup_name.
     * @param string $navgroup_name
     * @return null
     */
    protected function setNavByNgName($navgroup_name = '')
    {
        $o_ng = new NavgroupsModel($this->o_db);
        $ng_id = $o_ng->readNavgroupId($navgroup_name);
        $this->setNav($ng_id);
    }

    /**
     *  Returns an array with the values used primarily in the meta tags of the html.
     *  @return array
     */
    public function getPageValues()
    {
        $page_url     = $this->o_router->getRequestUri();
        $route_path   = $this->o_router->getRoutePath();
        $a_values1    = $this->o_page_model->read(['page_url' => $page_url]);
        $a_values2    = $this->o_page_model->read(['page_url' => $route_path]);

        if (isset($a_values1[0])) {
            $a_page_values = $a_values1[0];
        }
        elseif (isset($a_values2[0])) {
            $a_page_values = $a_values2[0];
        }
        else {
            return [
                'description'   => '',
                'title'         => '',
                'base_url'      => '/',
                'lang'          => 'en',
                'charset'       => 'utf-8',
                'public_dir'    => PUBLIC_DIR,
                'site_url'      => SITE_URL,
                'rights_holder' => RIGHTS_HOLDER
            ];
        }
        $base_url = $a_page_values['page_base_url'] == '/'
            ? SITE_URL
            : $a_page_values['page_base_url'];
        return [
            'description'   => $a_page_values['page_description'],
            'title'         => $a_page_values['page_title'],
            'base_url'      => $base_url,
            'lang'          => $a_page_values['page_lang'],
            'charset'       => $a_page_values['page_charset'],
            'public_dir'    => PUBLIC_DIR,
            'site_url'      => SITE_URL,
            'rights_holder' => RIGHTS_HOLDER
        ];
    }

    /**
     * @return array
     */
    public function getNav()
    {
        return $this->a_nav;
    }

    /**
     * Removes Navigation links that the person is not authorized to see.
     * @param array $a_nav If empty, this is a waste.
     * @return array
     */
    protected function removeUnauthorizedLinks(array $a_nav = []) {
        foreach($a_nav as $key => $a_item) {
            if (count($a_item['submenu']) > 0) {
                $a_nav[$key]['submenu'] = $this->removeUnauthorizedLinks($a_item['submenu']);
            }
            else {
                $this->o_routes_helper->setRouteParts($a_item['url']);
                $a_route_parts = $this->o_routes_helper->getRouteParts();
                if ($this->adm_level < $a_route_parts['min_auth_level']) {
                    unset($a_nav[$key]);
                }
            }
        }
        return $a_nav;
    }

    /**
     * Adds class to the nav array to indicate active menu.
     * @param array $a_nav
     * @return array
     */
    protected function specifyActiveMenu(array $a_nav = [])
    {
        $a_route_parts = $this->o_router->getRouteParts();
        $current_uri = $a_route_parts['request_uri'];
        foreach ($a_nav as $key => $a_item) {
            if (count($a_item['submenu']) > 0) {
                $a_nav[$key]['submenu'] = $this->specifyActiveMenu($a_item['submenu']);
            }
            else {
                if ($a_item['url'] == $current_uri) {
                    $a_item['class'] .= ' menu-active';
                }
                else {
                    $a_item['class'] .= ' menu-inactive';
                }
            }
        }
        return $a_nav;
    }
}
