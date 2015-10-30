<?php
/**
 *  @brief Common functions for the manager views.
 *  @file ManagerViewTraits.php
 *  @ingroup ritc_library Services
 *  @namespace Ritc/Library/Traits
 *  @class ManagerViewTraits
 *  @author William Reveal <bill@revealitconsulting.com>
 *  @version 1.0.1
 *  @date 2015-10-16 14:22:23
 *  @note A part of the RITC Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.1 - changed property name   - 10/16/2015 wer
 *      v1.0.0 - think it is working now - 10/05/2015 wer
 *      v0.1.0 - initial version         - 10/01/2015 wer
 *  </pre>
 */
namespace Ritc\Library\Traits;

use Ritc\Library\Helper\AuthHelper;
use Ritc\Library\Helper\RoutesHelper;
use Ritc\Library\Models\PageModel;
use Ritc\Library\Services\Di;

trait ManagerViewTraits
{
    protected $a_links;
    protected $adm_level;
    protected $o_auth;
    protected $o_db;
    protected $o_di;
    protected $o_router;
    protected $o_twig;

    /**
     *  The default setup for a view in the manager.
     *  @param Di $o_di
     */
    private function setupView(Di $o_di)
    {
        $this->setObjects($o_di);
        $this->setAuthLevel();
        $this->setLinks();
    }
    /**
     * Sets the standard used objects from the object injector.
     * @param Di $o_di
     */
    private function setObjects(Di $o_di)
    {
        $this->o_di     = $o_di;
        $this->o_auth   = new AuthHelper($o_di);
        $this->o_router = $o_di->get('router');
        $this->o_twig   = $o_di->get('twig');
        $this->o_db     = $o_di->get('db');
    }
    /**
     *  Sets the class property $login_id to a value of the highest role level found or 999 if not found.
     *  @param string $login_id
     */
    private function setAuthLevel($login_id = '')
    {
        if ($login_id != '') {
            $this->adm_level = $this->o_auth->getHighestRoleLevel($login_id);
        }
        elseif (isset($_SESSION['login_id'])) {
            $this->adm_level = $this->o_auth->getHighestRoleLevel($_SESSION['login_id']);
        }
        else {
            $this->adm_level = 999;
        }
    }
    /**
     *  Sets an array of links used for the manager home page and for the menus.
     */
    private function setLinks()
    {
        if ($this->adm_level == '') {
            $this->setAuthLevel();
        }
        $person_role_level = $this->adm_level;
        $current_route_path = $this->o_router->getRoutePath();
        $o_routes = new RoutesHelper($this->o_di, '');
        $a_links = include LIBRARY_CONFIG_PATH . '/links_config.php';
        foreach ($a_links as $key => $a_link) {
            $o_routes->setRouteParts($a_link['url']);
            $a_route_parts = $o_routes->getRouteParts();
            if ($person_role_level > $a_route_parts['min_role_level']) {
                unset($a_links[$key]);
            }
            if ($a_link['url'] == $current_route_path) {
                $a_links[$key]['class'] = 'menu-active';
            }
            else {
                $a_links[$key]['class'] = 'menu-inactive';
            }
        }
        $this->a_links = $a_links;
    }
    /**
     *
     */
    private function getPageValues()
    {
        $page_url = $this->o_router->getRequestUri();
        $o_page_model = new PageModel($this->o_db);
        $a_page_values = $o_page_model->read(['page_url' => $page_url]);
        if ($a_values !== false) {
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
        else {
            return [
                'description'   => 'Backend Manager',
                'title'         => 'Manager',
                'base_url'      => '/',
                'lang'          => 'en',
                'charset'       => 'utf-8',
                'public_dir'    => PUBLIC_DIR,
                'site_url'      => SITE_URL,
                'rights_holder' => RIGHTS_HOLDER
            ];
        }
    }
}
