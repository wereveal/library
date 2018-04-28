<?php
/**
 * @brief     Common functions for views.
 * @ingroup   lib_traits
 * @file      ViewTraits.php
 * @namespace Ritc\Library\Traits
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.2.0
 * @date      2018-04-20 10:06:17
 * @note <b>Change Log</b>
 * - v1.2.0         - added method to create values needed for pager template.                  - 2018-04-20 wer
 * - v1.1.1         - bug fix                                                                   - 2018-04-19 wer
 * - v1.1.0         - added new default twig values for asset dirs                              - 2018-04-12 wer
 * - v1.0.2         - more bug fixes for missed exception handlers                              - 2018-03-12 wer
 * - v1.0.1         - bug fix, missed an exception handler for ModelException                   - 2017-12-02 wer
 * - v1.0.0         - added method to handle TWIG exceptions (took out of beta finally)         - 2017-11-29 wer
 * - v1.0.0-beta.11 - refactoring elsewhere reflected here.                                     - 2017-06-20 wer
 * - v1.0.0-beta.10 - refactoring elsewhere reflected here.                                     - 2017-06-10 wer
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

use Ritc\Library\Exceptions\FactoryException;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Factories\TwigFactory;
use Ritc\Library\Helper\AuthHelper;
use Ritc\Library\Helper\ExceptionHelper;
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
     * The generic method to actually do the Twig rendering.
     * @param $tpl
     * @param $a_twig_values
     * @return string
     */
    public function renderIt($tpl, $a_twig_values)
    {
        if ($this->o_twig instanceof \Twig_Environment) {
            try {
                return $this->o_twig->render($tpl, $a_twig_values);
            }
            catch (\Twig_Error_Loader $e) {
                if (DEVELOPER_MODE) {
                    return 'Error: ' . $e->getMessage();
                }
                else {
                    return '';
                }
            }
            catch(\Twig_Error_Syntax $e) {
                if (DEVELOPER_MODE) {
                    return 'Error: ' . $e->getMessage();
                }
                else {
                    return '';
                }
            }
            catch(\Twig_Error_Runtime $e) {
                if (DEVELOPER_MODE) {
                    return 'Error: ' . $e->getMessage();
                }
                else {
                    return '';
                }
            }
        }
        else {
            return 'Error: instance of Twig not created.';
        }
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
        return $this->renderIt($tpl, $a_twig_values);
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
            $a_message = ViewHelper::fullMessage(['message' => '']);
        }
        else {
            $a_message = ViewHelper::fullMessage($a_message);
        }

        $a_values = [
            'a_message'      => $a_message,
            'tolken'         => $_SESSION['token'],
            'form_ts'        => $_SESSION['idle_timestamp'],
            'hobbit'         => '',
            'a_menus'        => $a_menus,
            'adm_lvl'        => $this->adm_level,
            'twig_prefix'    => TWIG_PREFIX,
            'lib_prefix'     => LIB_TWIG_PREFIX,
            'assets_dir'     => ASSETS_DIR,
            'css_dir'        => CSS_DIR,
            'files_dir'      => FILES_DIR,
            'fonts_dir'      => FONTS_DIR,
            'images_dir'     => IMAGES_DIR,
            'js_dir'         => JS_DIR,
            'vendor_dir'     => VENDOR_ASSETS,
            'public_dir'     => PUBLIC_DIR,
            'site_url'       => SITE_URL,
            'rights_holder'  => RIGHTS_HOLDER,
            'copyright_date' => COPYRIGHT_DATE
        ];
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
        try {
            $ng_id = $o_ng->readIdByName($navgroup_name);
        }
        catch (ModelException $e) {
            $ng_id = 1;
        }
        $this->setNav($ng_id);
    }

    /**
     * Creates and sets the o_twig property.
     * See the TwigFactory::getTwig method for details.
     * @param string|array $twig_config \ref twigfactory
     * @param string       $name
     * @throws \Ritc\Library\Exceptions\FactoryException
     */
    public function setTwig($twig_config = 'twig_config.php', $name = 'main')
    {
        try {
            $o_twig = TwigFactory::getTwig($twig_config, $name);
        }
        catch (FactoryException $e) {
            throw new FactoryException($e->errorMessage(), $e->getCode());
        }
        if (!$o_twig instanceof \Twig_Environment) {
            throw new FactoryException('Could not create twig object', ExceptionHelper::getCodeNumberFactory('start'));
        }
        $this->o_di->set('twig_' . $name, $o_twig);
        $this->o_twig = $o_twig;
    }

    /**
     * Returns an array with the values used primarily in the meta tags of the html.
     * @param int|string $url_id
     * @return array
     */
    public function getPageValues($url_id = -1)
    {
        $o_page_model = new PageComplexModel($this->o_di);
        $o_url = new UrlsModel($this->o_db);
        if (is_numeric($url_id)) {
            if ($url_id < 1) {
                $url_id = $this->o_router->getUrlId();
            }
        }
        else {
            try {
                $a_urls = $o_url->read(['url_text' => $url_id]);
                $url_id = empty($a_urls[0]['url_id'])
                    ? $this->o_router->getUrlId()
                    : $a_urls[0]['url_id'];
            }
            catch (ModelException $e) {
                $url_id = $this->o_router->getUrlId();
            }
        }
        try {
            $a_values = $o_page_model->readPageValuesByUrlId($url_id);
            if (isset($a_values[0])) {
                $a_page_values = $a_values[0];
            }
            else {
                $a_page_values = [];
            }
        }
        catch (ModelException $e) {
            $a_page_values = [];
        }
        if (empty($a_page_values)) {
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
     * Creates the values needed for the pager template.
     * @param array $a_parameters
     * @return array
     */
    protected function createNumericPagerValues($a_parameters = [])
    {
        if (empty($a_parameters['start_record'])
            || empty($a_parameters['records_to_display'])
            || empty($a_parameters['total_records'])
            || empty($a_parameters['href'])
        ) {
            return [];
        }

        $a_pager            = [];
        $start_record       = $a_parameters['start_record'];
        $records_to_display = $a_parameters['records_to_display'];
        $total_records      = $a_parameters['total_records'];
        $href               = $a_parameters['href'];
        if ($records_to_display >= $total_records) {
            return [];
        }
        $get_stuff = '';
        if (!empty($a_parameters['get_params'])) {
            foreach($a_parameters['get_params'] as $key => $value) {
                $get_stuff .= $get_stuff == ''
                    ? '?' . $key . '=' . $value
                    : '&' . $key . '=' . $value;

            }
        }
        $use_page_numbers   = !empty($a_parameters['use_page_numbers'])
            ? $a_parameters['use_page_numbers']
            : true;
        $previous_value     = $start_record == 1 || $start_record - $records_to_display < 1
            ? 1
            : $start_record - $records_to_display;
        $next_value         = $start_record > 1
            ? $start_record + $records_to_display
            : $records_to_display
        ;
        $number_of_pages     = round($total_records / $records_to_display);
        $this_page           = round($start_record / $records_to_display) + 1;
        $before_this_page = $this_page - 4;
        if ($before_this_page <= 0) {
            $i_start = 1;
        }
        else {
            $i_start = $before_this_page;
        }
        $a_pager['first']    = $href . "/1/" . $get_stuff;
        $a_pager['previous'] = $href . '/' . $previous_value . '/' . $get_stuff;
        $a_pager['next']     = $href . '/' . $next_value . '/' . $get_stuff;
        $a_pager['links']    = [];
        $a_pager['last']     = $href . '/' . (($number_of_pages - 1) * $records_to_display) . '/' . $get_stuff;
        $display_links = !empty($a_parameters['display_links'])
            ? $a_parameters['display_links'] == 'all'
                ? $number_of_pages
                : $a_parameters['display_links']
            : 11;
        if ($start_record == 1) {
            $a_pager['previous'] = '';
            $a_pager['first'] = '';
        }
        if ($start_record == $records_to_display) {
            $a_pager['previous'] = $href . '/1/' . $get_stuff;
        }
        $i_end = $i_start + $display_links;
        if ($i_end > $number_of_pages) {
            $i_start = $number_of_pages - 10 < 1
                ? 1
                : $number_of_pages - 10;
            $i_end = $number_of_pages;
            if ($i_end == $this_page) {
                $a_pager['next'] = '';
                $a_pager['last'] = '';
            }
        }
        for ($i = $i_start; $i <= $i_end; $i++) {
            if ($use_page_numbers) {
                $text = $i;
            }
            else {
                if ($i == 1) {
                    $text = 1;
                }
                else {
                    $text = ($i - 1) * $records_to_display;
                }
            }
            if ($i === 1) {
                $link = $href . "/{$i}/" . $get_stuff;
            }
            elseif ($this_page == $i) {
                $link = '';
            }
            else {
                $link = $href . '/' . (($i - 1) * $records_to_display) . '/' . $get_stuff;
            }
            if ($i == $start_record || ($start_record == 1 && $i == 0)) {
                $link = '';
            }
            $a_pager['links'][] = [
                'href' => $link,
                'text' => $text
            ];
        }
        return $a_pager;
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
     * @param string|int $navgroup Defaults to the default navgroup.
     *                             This can be either the navgroup id or name.
     * @return array
     */
    protected function readNav($navgroup = '')
    {
        $o_ng = new NavgroupsModel($this->o_db);
        if (empty($navgroup)) {
            try {
                $navgroup = $o_ng->retrieveDefaultId();
            }
            catch (ModelException $e) {
                return [];
            }
        }
        if (!is_numeric($navgroup)) {
            try {
                $navgroup = $o_ng->readIdByName($navgroup);
                if (empty($navgroup)) {
                    return [];
                }
            }
            catch (ModelException $e) {
                return [];
            }
        }
        try {
            return $this->o_nav->getNavList($navgroup);
        }
        catch (ModelException $e) {
            return [];
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
            $order_number = !empty($a_link['order'])
                ? (int)$a_link['order']
                : 99;
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
