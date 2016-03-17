<?php
/**
 * @brief       Common functions for the manager views.
 * @depreciated Use ViewTraits instead.
 * @ingroup     lib_traits
 * @file        ManagerViewTraits.php
 * @namespace   Ritc\Library\Traits
 * @author      William E Reveal <bill@revealitconsulting.com>
 * @version     1.1.0
 * @date        2015-12-15 14:36:42
 * @note <b>Change Log</b>
 * - v1.1.0 - manager links can be in two places. - 12/15/2015 wer
 * - v1.0.2 - bug fix                             - 11/24/2015 wer
 * - v1.0.1 - changed property name               - 10/16/2015 wer
 * - v1.0.0 - think it is working now             - 10/05/2015 wer
 * - v0.1.0 - initial version                     - 10/01/2015 wer
 */
namespace Ritc\Library\Traits;

use Ritc\Library\Helper\AuthHelper;
use Ritc\Library\Helper\RoutesHelper;
use Ritc\Library\Models\PageModel;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Services\Router;

/**
 * Class ManagerViewTraits
 * @class   ManagerViewTraits
 * @package Ritc\Library\Traits
 */
trait ManagerViewTraits
{
    /** @var array */
    protected $a_links;
    /** @var int */
    protected $adm_level;
    /** @var AuthHelper */
    protected $o_auth;
    /** @var DbModel */
    protected $o_db;
    /** @var Di */
    protected $o_di;
    /** @var Router */
    protected $o_router;
    /** @var \Twig_Environment */
    protected $o_twig;

    /**
     * The default setup for a view in the manager.
     * @param Di $o_di
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
     * Sets the class property $adm_level to a value of the highest auth level
     * found or 0 if not found.
     * @param string $login_id
     */
    private function setAuthLevel($login_id = '')
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
     * Sets an array of links used for the manager home page and for the menus.
     * @return null
     */
    private function setLinks()
    {
        $meth = __METHOD__ . '.';
        if ($this->adm_level == '') {
            $this->setAuthLevel();
        }
        $person_auth_level = $this->adm_level;
        $this->logIt('Person adm level: ' . $person_auth_level, LOG_OFF, $meth . __LINE__);
        $current_route_path = $this->o_router->getRoutePath();
        $o_routes = new RoutesHelper($this->o_di, '');
        if (file_exists(APP_CONFIG_PATH . '/manager_links.php')) {
            $manager_links_file = APP_CONFIG_PATH . '/manager_links.php';
        }
        elseif (file_exists(LIBRARY_CONFIG_PATH . '/manager_links.php')) {
            $manager_links_file = LIBRARY_CONFIG_PATH . '/manager_links.php';
        }
        else {
            $manager_links_file = '';
        }
        $a_links = $manager_links_file != ''
            ? include $manager_links_file
            : array();
        $this->logIt('In Set Links: ' . var_export($a_links, TRUE), LOG_OFF, $meth . __LINE__);
        foreach ($a_links as $key => $a_link) {
            $o_routes->setRouteParts($a_link['url']);
            $a_route_parts = $o_routes->getRouteParts();

            $this->logIt('Route Parts: ' . var_export($a_route_parts, TRUE), LOG_OFF, $meth . __LINE__);
            if ($person_auth_level > $a_route_parts['min_auth_level']) {
                unset($a_links[$key]);
            }
            else {
                if ($a_link['url'] == $current_route_path) {
                    $a_links[$key]['class'] = 'menu-active';
                }
                else {
                    $a_links[$key]['class'] = 'menu-inactive';
                }
            }
        }
        $this->a_links = $a_links;
    }

    /**
     * Returns an array with the values used primarily in the meta tags of the html.
     * @return array
     */
    private function getPageValues()
    {
        $page_url     = $this->o_router->getRequestUri();
        $route_path   = $this->o_router->getRoutePath();
        $o_page_model = new PageModel($this->o_db);
        $a_values1    = $o_page_model->read(['page_url' => $page_url]);
        $a_values2    = $o_page_model->read(['page_url' => $route_path]);

        if (isset($a_values1[0])) {
            $a_page_values = $a_values1[0];
        }
        elseif (isset($a_values2[0])) {
            $a_page_values = $a_values2[0];
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
    public function getLinks()
    {
        return $this->a_links;
    }
}
