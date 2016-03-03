<?php
/**
 *  @brief     Common functions for views.
 *  @ingroup   ritc_library traits
 *  @file      ViewTraits.php
 *  @namespace Ritc\Library\Traits
 *  @class     ViewTraits
 *  @author    William E Reveal <bill@revealitconsulting.com>
 *  @version   1.0.0 β1
 *  @date      2016-02-22 20:15:04
 *  @note <pre><b>Change Log</b>
 *      v1.0.0 β1  - inital version - 02/22/2016 wer
 *  </pre>
 */
namespace Ritc\Library\Traits;

use Ritc\Library\Helper\AuthHelper;
use Ritc\Library\Models\NavComplexModel;
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
        $this->o_di         = $o_di;
        $this->o_router     = $o_di->get('router');
        $this->o_twig       = $o_di->get('twig');
        $this->o_db         = $o_di->get('db');
        $this->o_auth       = new AuthHelper($o_di);
        $this->o_page_model = new PageModel($this->o_db);
        $this->o_nav        = new NavComplexModel($this->o_db);
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
     *  Creates an array of links used for menus and other navigation areas.
     *  @param int $nav_group optional, defaults to the 1 nav group.
     *  @return array
     */
    protected function createNav($nav_group = 1)
    {
        if ($this->adm_level == '') {
            $this->setAdmLevel();
        }
        $a_nav = $this->o_nav->createNavArray($nav_group);
        $a_nav = $this->removeUnauthorizedLinks($a_nav);
        return $a_nav;
    }

    /**
     * Sets the class property a_nav.
     * Uses the createNav method to do so.
     * @param int $nav_group optional, defaults to 1
     * @return null
     */
    protected function setNav($nav_group = 1)
    {
        $this->a_nav = $this->createNav($nav_group);
    }

    /**
     * Removes Navigation links that the person is not authorized to see.
     * @param array $a_nav If empty, this is a waste.
     * @return array
     */
    protected function removeUnauthorizedLinks(array $a_nav = []) {
        $current_route_path = $this->o_router->getRoutePath();
        $a_route_parts = $this->o_router->getRouteParts();
        foreach($a_nav as $key => $a_item) {
            if (count($a_item['submenu']) > 0) {
                $a_nav[$key]['submenu'] = $this->removeUnauthorizedLinks($a_item['submenu']);
            }
            else {
                if ($this->adm_level < $a_route_parts['min_auth_level']) {
                    unset($a_nav[$key]);
                }
                else {
                    if ($a_nav['url'] = $current_route_path) {
                        $a_nav[$key]['class'] .= ' menu-active';
                    }
                    else {
                        $a_nav[$key]['class'] .= ' menu-inactive';
                    }
                }
            }
        }
        return $a_nav;
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
}
