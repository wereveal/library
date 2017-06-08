<?php
/**
 * @brief     Common functions for views.
 * @ingroup   lib_traits
 * @file      ViewTraits.php
 * @namespace Ritc\Library\Traits
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-beta.8
 * @date      2017-05-27 17:57:43
 * @note <b>Change Log</b>
 * - v1.0.0-beta.9  - modified createDefaultTwigValues and getPageValues to optionally take a   - 2017-05-30 wer
 *                    url_id to in essence change the page values based on a different url from
 *                    the one called.
 * - v1.0.0-beta.8  - added renderError method to render the error page.                        - 2017-05-27 wer
 * - v1.0.0-beta.7  - database change to page table reflected here.                             - 2017-05-10 wer
 * - v1.0.0-beta.6  - added setTwig method to allow a different twig environment to be used.    - 2017-03-14 wer
 * - v1.0.0-beta.5  - moved some functionality from getPageValues to createDefaultTwigValues    - 2017-03-13 wer
 * - v1.0.0-beta.4  - removed twigLoader which apparently didn't really work                    - 2017-02-11 wer
 * - v1.0.0-beta.3  - removed LogitTraits from this trait, bug fix in twigLoader                - 2017-02-07 wer
 *                    There were times when another trait also used LogitTraits
 *                    and was causing conflicts.
 * - v1.0.0-beta.2  - added lib_prefix for twig prefixes as a default                           - 2017-01-27 wer
 * = v1.0.0-beta.1  - This should have come out of alpha a while back                           - 2017-01-24 wer
 *                    Added twigLoader method
 * - v1.0.0-alpha.4 - Added new method createDefaultTwigValues                                  - 2016-04-15 wer
 * - v1.0.0-alpha.3 - Navigation links are now sorted properly                                  - 2016-04-08 wer
 * - v1.0.0-alpha.2 - Use LogitTraits now                                                       - 2016-04-01 wer
 *                    Views that use this trait no longer need to 'use' LogitTraits
 *                    May cause some complaints from those that don't fix this.
 * - v1.0.0-alpha.1 - close to working version                                                  - 2016-03-10 wer
 * - v1.0.0-alpha.0 - inital version                                                            - 2016-02-22 wer
 */
namespace Ritc\Library\Traits;

use Ritc\Library\Factories\TwigFactory;
use Ritc\Library\Helper\AuthHelper;
use Ritc\Library\Helper\RoutesHelper;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Models\NavComplexModel;
use Ritc\Library\Models\NavgroupsModel;
use Ritc\Library\Models\PageComplexModel;
use Ritc\Library\Models\UrlsModel;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Services\Router;
use Ritc\Library\Services\Session;

/**
 * Class ViewTraits
 * @class   ViewTraits
 * @package Ritc\Library\Traits
 */
trait ViewTraits
{
    /** @var array */
    protected $a_nav;
    /** @var int */
    protected $adm_level;
    /** @var AuthHelper */
    protected $o_auth;
    /** @var DbModel */
    protected $o_db;
    /** @var Di */
    protected $o_di;
    /** @var NavComplexModel */
    protected $o_nav;
    /** @var Router */
    protected $o_router;
    /** @var \Ritc\Library\Helper\RoutesHelper */
    protected $o_routes_helper;
    /** @var  Session */
    protected $o_session;
    /** @var \Twig_Environment */
    protected $o_twig;

    ### Main Public Methods ###
    /**
     * The default setup for a view.
     * @param Di $o_di
     */
    public function setupView(Di $o_di)
    {
        $this->setOProperties($o_di);
        $this->setAdmLevel();
        $this->setNav();
    }

    /**
     * Renders the error page.
     * @param array $a_message
     * @return string
     */
    public function renderError(array $a_message = [])
    {
        $a_twig_values = $this->createDefaultTwigValues($a_message);
        $a_twig_values['tpl'] = 'error';
        $tpl = $this->createTplString($a_twig_values);
        return $this->o_twig->render($tpl, $a_twig_values);
    }

    /**
     * Retrieves the navigation info for the nav group.
     * Does not set the class property a_nav. Use the setNav method to do that.
     * @param string|int $nav_group Defaults to the default navgroup.
     *                              This can be either the navgroup id or name.
     * @return array
     */
    public function retrieveNav($nav_group = '')
    {
        if ($this->adm_level == '') {
            $this->setAdmLevel();
        }
        $a_nav = $this->readNav($nav_group);
        $a_nav = $this->removeUnauthorizedLinks($a_nav);
        $a_nav = $this->createSubmenu($a_nav);
        $a_nav = $this->sortTopLevel($a_nav);
        return $a_nav;
    }

    /**
     * Creates some commonly used values to pass into a twig template.
     * @param array      $a_message Optional, defaults to []. Allows a message to be passed.
     * @param int|string $url_id    Optional, defaults to -1 which then uses current url_id.
     * @return array
     */
    public function createDefaultTwigValues(array $a_message = [], $url_id = -1)
    {
        $meth = __METHOD__ . '.';
        $a_page_values = $this->getPageValues($url_id);
        $log_message = 'page values ' . var_export($a_page_values, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        $a_menus = $this->retrieveNav($a_page_values['ng_id']);
        if (empty($a_message)) {
            $a_message = ViewHelper::messageProperties(['message' => '']);
        }
        else {
            $a_message = ViewHelper::messageProperties($a_message);
        }

        $a_values = array(
            'a_message'      => $a_message,
            'tolken'         => $_SESSION['token'],
            'form_ts'        => $_SESSION['idle_timestamp'],
            'hobbit'         => '',
            'a_menus'        => $a_menus,
            'adm_lvl'        => $this->adm_level,
            'twig_prefix'    => TWIG_PREFIX,
            'lib_prefix'     => LIB_TWIG_PREFIX,
            'public_dir'     => PUBLIC_DIR,
            'site_url'       => SITE_URL,
            'rights_holder'  => RIGHTS_HOLDER,
            'copyright_date' => COPYRIGHT_DATE
        );
        $a_values = array_merge($a_values, $a_page_values);
        return $a_values;
    }

    /**
     * Creates the template string used by \Twig_Environment::render.
     * @param array $a_twig_values
     * @return string
     */
    public function createTplString(array $a_twig_values = [])
    {
        if (empty($a_twig_values)) { return ''; }
        $page_prefix = empty($a_twig_values['page_prefix'])
            ? $a_twig_values['twig_prefix']
            : $a_twig_values['page_prefix'];

        return '@'
            . $page_prefix
            . $a_twig_values['twig_dir']
            . '/'
            .  $a_twig_values['tpl']
            . '.twig';
    }

    ### SETters and GETters ###
    /**
     * Sets the standard used objects from the object injector.
     * @param Di $o_di
     */
    protected function setOProperties(Di $o_di)
    {
        if (empty($this->o_di)) {
            $this->o_di = $o_di;
        }
        if (empty($this->o_router)) {
            $this->o_router = $o_di->get('router');
        }
        if (empty($this->o_twig)) {
            $this->o_twig = $o_di->get('twig');
        }
        if (empty($this->o_db)) {
            $this->o_db = $o_di->get('db');
        }
        if (empty($this->o_session)) {
            $this->o_session = $o_di->get('session');
        }
        if (empty($this->o_auth)) {
            $this->o_auth = new AuthHelper($o_di);
        }
        if (empty($this->o_nav)) {
            $this->o_nav = new NavComplexModel($this->o_db);
        }
        if (empty($this->o_routes_helper)) {
            $this->o_routes_helper = new RoutesHelper($o_di);
        }
        if (defined('DEVELOPER_MODE') && DEVELOPER_MODE) {
            $o_elog = $o_di->get('elog');
            $this->o_nav->setElog($o_elog);
        }
    }

    /**
     * Sets the class property $adm_level to a value of the highest auth level
     * found or 0 if not found.
     * @param string $login_id
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
     * Uses the retrieveNav method to do so.
     * @param int|string $nav_group optional, defaults to 1
     */
    protected function setNav($nav_group = 1)
    {
        $this->a_nav = $this->retrieveNav($nav_group);
    }

    /**
     * Sets the property a_nav array by the navgroup_name.
     * @param string $navgroup_name
     */
    protected function setNavByNgName($navgroup_name = '')
    {
        $o_ng = new NavgroupsModel($this->o_db);
        $ng_id = $o_ng->readNavgroupId($navgroup_name);
        $this->setNav($ng_id);
    }

    /**
     * Creates and sets the o_twig property.
     * See the TwigFactory::getTwig method for details.
     * @param string|array $twig_config \ref twigfactory
     * @param string       $name
     * @param bool         $use_main
     */
    public function setTwig($twig_config = 'twig_config.php', $name = 'main')
    {
        $o_twig = TwigFactory::getTwig($twig_config, $name);
        if (!$o_twig instanceof \Twig_Environment) {
            die("Could not create a new TwigEnviornment");
        }
        $this->o_di->set('twig_' . $name, $o_twig);
        $this->o_twig = $o_twig;
    }

    /**
     * Returns an array with the values used primarily in the meta tags of the html.
     * @return array
     */
    public function getPageValues($url_id = -1)
    {
        $meth = __METHOD__ . '.';
        $o_page_model = new PageComplexModel($this->o_di);
        $o_url = new UrlsModel($this->o_db);
        if (is_numeric($url_id)) {
            if ($url_id < 1) {
                $url_id = $this->o_router->getUrlId();
            }
        }
        else {
            $a_urls = $o_url->read(['url_text' => $url_id]);
            $url_id = $a_urls[0]['url_id'];
        }
        $a_values = $o_page_model->readPageValuesByUrlId($url_id);
        $log_message = 'Page Values By URL Id:  ' . var_export($a_values, TRUE);
        $this->logIt($log_message, LOG_ON, $meth . __LINE__);

        if (isset($a_values[0])) {
            $a_page_values = $a_values[0];
        }
        else {
            return [
                'page_id'        => 0,
                'url_id'         => 0,
                'url_scheme'     => 'https',
                'ng_id'          => 1,
                'page_url'       => '/',
                'description'    => '',
                'title'          => '',
                'lang'           => 'en',
                'charset'        => 'utf-8',
                'page_prefix'    => TWIG_PREFIX,
                'twig_dir'       => 'pages',
                'tpl'            => 'index',
                'base_url'       => '/',
            ];
        }
        $base_url = $a_page_values['page_base_url'] == '/'
            ? SITE_URL
            : SITE_URL . $a_page_values['page_base_url'];

        return [
            'page_id'     => $a_page_values['page_id'],
            'url_id'      => $a_page_values['url_id'],
            'url_scheme'  => $a_page_values['url_scheme'],
            'ng_id'       => $a_page_values['ng_id'],
            'page_url'    => $a_page_values['url_text'],
            'description' => $a_page_values['page_description'],
            'title'       => $a_page_values['page_title'],
            'lang'        => $a_page_values['page_lang'],
            'charset'     => $a_page_values['page_charset'],
            'page_prefix' => $a_page_values['twig_prefix'],
            'twig_dir'    => $a_page_values['twig_dir'],
            'tpl'         => $a_page_values['tpl_name'],
            'base_url'    => $base_url
        ];
    }

    /**
     * @return array
     */
    public function getNav()
    {
        return $this->a_nav;
    }

    ### Utilities ###
    /**
     * Utility to create a new unique integer value for the array passed in.
     * Recursive in nature, will find an integer value not in the array and return it.
     * @param array $a_used
     * @param int   $value
     * @return int
     */
    protected function createNewOrderNumber(array $a_used = [], $value = 0)
    {
        if (array_search($value, $a_used) === false) {
            return (int) $value;
        }
        elseif (array_search($value + 1, $a_used) === false) {
            $new_value = $value + 1;
            return (int) $new_value;
        }
        else {
            return $this->createNewOrderNumber($a_used, $value + 1);
        }
    }

    /**
     * Turns a flat db result into a multi-dimensional array for navigation.
     * @param array $a_nav
     * @return array
     */
    protected function createSubmenu(array $a_nav = [])
    {
        if ($a_nav == []) {
            return [];
        }
        $a_new_nav = [];
        foreach ($a_nav as $a_link) {
            if ($a_link['level'] == 1) {
                $this_link_id = isset($a_link['nav_id'])
                    ? $a_link['nav_id']
                    : 0;
                $a_new_nav[$this_link_id] = $a_link;
                $a_new_nav[$this_link_id]['submenu'] = [];
            }
            else {
                $a_new_nav[$a_link['parent_id']]['submenu'][] = $a_link;
            }
        }
        return $a_new_nav;
    }

    /**
     * Retrieves the raw nav records for the nav group.
     * @param string|int $nav_group Defaults to the default navgroup.
     *                              This can be either the navgroup id or name.
     * @return array
     */
    protected function readNav($nav_group = '')
    {
        $o_ng = new NavgroupsModel($this->o_db);
        if ($nav_group == '') {
            $nav_group = $o_ng->retrieveDefaultNavgroup();
        }
        if (is_numeric($nav_group)) {
            return $this->o_nav->getNavList($nav_group);
        }
        else {
            return $this->o_nav->getNavListByName($nav_group);
        }
    }

    /**
     * Removes Navigation links that the person is not authorized to see.
     * @param array $a_nav If empty, this is a waste.
     * @return array
     */
    protected function removeUnauthorizedLinks(array $a_nav = []) {
        foreach($a_nav as $key => $a_item) {
            if (isset($a_item['submenu']) && count($a_item['submenu']) > 0) {
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
     * Sorts the top level nav array by the top level nav order.
     * @param array $a_nav
     * @return array
     */
    protected function sortTopLevel(array $a_nav = [])
    {
        // first see if the links have duplicate sort order
        $a_used_order = [];
        $a_new_nav = [];
        foreach ($a_nav as $key => $a_link) {
            $order_number = (int) $a_link['order'];
            if (array_search($order_number, $a_used_order) !== false) {
                $new_order_number = (int) $this->createNewOrderNumber($a_used_order, $order_number);
                $a_used_order[] = $new_order_number;
                $a_new_nav[$new_order_number] = $a_link;
            }
            else {
                $a_used_order[] = $order_number;
                $a_new_nav[$order_number] = $a_link;
            }
        }
        ksort($a_new_nav);
        return $a_new_nav;
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
