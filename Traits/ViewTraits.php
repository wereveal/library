<?php
/**
 * Trait ViewTraits
 * @package Ritc_Library
 */
namespace Ritc\Library\Traits;

use TypeError;
use Ritc\Library\Exceptions\FactoryException;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Factories\TwigFactory;
use Ritc\Library\Helper\AuthHelper;
use Ritc\Library\Helper\CacheHelper;
use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Helper\RoutesHelper;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Models\ContentComplexModel;
use Ritc\Library\Models\GroupsModel;
use Ritc\Library\Models\NavComplexModel;
use Ritc\Library\Models\NavgroupsModel;
use Ritc\Library\Models\PageComplexModel;
use Ritc\Library\Models\UrlsModel;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Services\Elog;
use Ritc\Library\Services\Router;
use Ritc\Library\Services\Session;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError as TwigErrorLoader;
use Twig\Error\SyntaxError as TwigErrorSyntax;
use Twig\Error\RuntimeError as TwigErrorRuntime;

/**
 * Common functions for views.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v2.1.0
 * @date    2018-12-18 17:10:27
 * @change_log
 * - v2.1.0         - Added the shared content to the default twig values                       - 2018-12-18 wer
 * - v2.0.0         - Added caching of some data that is used commonly in a view.               - 2018-05-14 wer
 *                    Added a new method for setting the url_id based on record id
 *                    which fixes a bug.
 * - v1.2.0         - added method to create values needed for pager template.                  - 2018-04-20 wer
 * - v1.1.0         - added new default twig values for asset dirs                              - 2018-04-12 wer
 * - v1.0.0         - added method to handle TWIG exceptions (took out of beta finally)         - 2017-11-29 wer
 * - v1.0.0-beta.11 - refactoring elsewhere reflected here.                                     - 2017-06-20 wer
 * = v1.0.0-beta.1  - This should have come out of alpha a while back                           - 2017-01-24 wer
 *                    Added twigLoader method
 * - v1.0.0-alpha.4 - Added new method createDefaultTwigValues                                  - 2016-04-15 wer
 * - v1.0.0-alpha.0 - initial version                                                           - 2016-02-22 wer
 */
trait ViewTraits
{
    /** @var array */
    protected $a_nav;
    /** @var int */
    protected $adm_level;
    /** @var string $cache_type */
    protected $cache_type;
    /** @var AuthHelper */
    protected $o_auth;
    /** @var CacheHelper $o_cache */
    protected $o_cache;
    /** @var DbModel */
    protected $o_db;
    /** @var Di */
    protected $o_di;
    /** @var NavComplexModel */
    protected $o_nav;
    /** @var Router */
    protected $o_router;
    /** @var RoutesHelper */
    protected $o_routes_helper;
    /** @var  Session */
    protected $o_session;
    /** @var TwigEnvironment */
    protected $o_twig;
    /** @var bool */
    private $use_cache;

    ### Main Public Methods ###
    /**
     * The default setup for a view.
     *
     * @param Di $o_di
     */
    public function setupView(Di $o_di):void
    {
        $this->setOProperties($o_di);
        $this->setAdmLevel();
        $this->setNav();
    }

    /**
     * The generic method to actually do the Twig rendering.
     *
     * @param string $tpl
     * @param array  $a_twig_values
     * @return string
     */
    public function renderIt($tpl = '', array $a_twig_values = []):?string
    {
        if (empty($tpl) || empty($a_twig_values)) {
            return 'Error: missing values.';
        }
        if ($this->o_twig instanceof TwigEnvironment) {
            try {
                return $this->o_twig->render($tpl, $a_twig_values);
            }
            catch (TwigErrorLoader $e) {
                if (DEVELOPER_MODE) {
                    return 'Error: ' . $e->getMessage();
                }
                return '';
            }
            catch(TwigErrorSyntax $e) {
                if (DEVELOPER_MODE) {
                    return 'Error: ' . $e->getMessage();
                }
                return '';
            }
            catch(TwigErrorRuntime $e) {
                if (DEVELOPER_MODE) {
                    return 'Error: ' . $e->getMessage();
                }
                return '';
            }
            catch(TypeError $e) {
                if (DEVELOPER_MODE) {
                    return 'Error: ' . $e->getMessage();
                }
                return '';
            }
        }
        else {
            return 'Error: instance of Twig not created.';
        }
    }

    /**
     * Renders the error page.
     *
     * @param array $a_message
     * @return string
     */
    public function renderError(array $a_message = []):string
    {
        $a_twig_values = $this->createDefaultTwigValues($a_message);
        $a_twig_values['tpl'] = 'error';
        $tpl = $this->createTplString($a_twig_values);
        return $this->renderIt($tpl, $a_twig_values);
    }

    /**
     * Retrieves the navigation info for the nav group.
     * Does not set the class property a_nav. Use the setNav method to do that.
     *
     * @param string|int $nav_group Defaults to the default navgroup.
     *                              This can be either the navgroup id or name.
     * @return array
     */
    public function retrieveNav($nav_group = ''):array
    {
        $cache_key = 'nav.values.ng_id.' . $nav_group;
        if ($this->use_cache) {
            $a_nav = $this->o_cache->get($cache_key);
        }
        else {
            $a_nav = [];
        }
        if (!is_array($a_nav) || empty($a_nav)) {
            if ($this->adm_level === '') {
                $this->setAdmLevel();
            }
            $a_nav = $this->readNav($nav_group);
            $a_nav = $this->removeUnauthorizedLinks($a_nav);
            $a_nav = $this->createSubmenu($a_nav);
            $a_nav = $this->sortTopLevel($a_nav);
            if ($this->use_cache) {
                $this->o_cache->set($cache_key, $a_nav, 'nav');
            }
        }
        return $a_nav;
    }

    /**
     * Builds the sitemap array or an error message.
     *
     * @param string $child_levels Optional, defaults to 'all', options are 'all', 'one', 'none'.
     *                             All = all chilren, one = one child level, none = no children.
     *                             Note that for now, only two children levels are available.
     * @return array
     */
    public function buildSitemapArray($child_levels = 'all'):array
    {
        $a_sitemap = [];
        $date_key = 'sitemap.html.date';
        $value_key = 'sitemap.html.value';
        if ($this->use_cache) {
            $date = $this->o_cache->get($date_key);
            if ($date === date('Ymd')) {
                $a_values = $this->o_cache->get($value_key);
                if (!empty($a_values)) {
                    $a_sitemap = $a_values;
                }
            }
        }
        if (empty($a_sitemap)) {
            $a_navgroups = ['Sitemap'];
            if ($this->o_auth->isLoggedIn()) {
                $auth_level = $this->o_session->getVar('adm_lvl');
                if ($auth_level >= 9) {
                    $a_navgroups[] = 'ConfigLinks';
                    $a_navgroups[] = 'ManagerLinks';
                    $a_navgroups[] = 'EditorLinks';
                }
                elseif ($auth_level >= 6) {
                    $a_navgroups[] = 'ManagerLinks';
                    $a_navgroups[] = 'EditorLinks';
                }
                elseif ($auth_level >= 4) {
                    $a_navgroups[] = 'EditorLinks';
                }
            }
            else {
                $auth_level = 0;
            }
            try {
                $a_sitemap = $this->o_nav->getSitemap($a_navgroups, $auth_level, $child_levels);
            }
            catch (ModelException $e) {
                $a_sitemap = ViewHelper::errorMessage('Unable to retrieve the sitemap.');
            }
            if ($this->use_cache && empty($a_sitemap['message'])) {
                $this->o_cache->set($date_key, date('Ymd'), 'sitemap');
                $this->o_cache->set($value_key, $a_sitemap, 'sitemap');
            }
        }
        return $a_sitemap;
    }

    /**
     * Creates some commonly used values to pass into a twig template.
     *
     * @param array      $a_message Optional, defaults to []. Allows a message to be passed.
     * @param int|string $url_id    Optional, defaults to -1 which then uses current url_id.
     * @return array
     */
    public function createDefaultTwigValues(array $a_message = [], $url_id = -1):array
    {
        $a_page_values = [];
        $a_auth_levels = [];
        if ((int) $url_id < 1) {
            $url_id = $this->urlId($url_id);
        }
        $page_cache_key = 'page.values.url_id.' . $url_id;
        $group_cache_key = 'groups.values.auth_levels';
        if ($this->use_cache) {
            $a_page_values   = $this->o_cache->get($page_cache_key);
            $a_auth_levels   = $this->o_cache->get($group_cache_key);
        }
        if (!is_array($a_page_values) || empty($a_page_values)) {
            $a_page_values = $this->getPageValues($url_id);
            if ($this->use_cache) {
                $this->o_cache->set($page_cache_key, $a_page_values, 'page');
            }
        }
        if (!is_array($a_auth_levels) || empty($a_auth_levels)) {
            $a_auth_levels = [];
            $o_group = new GroupsModel($this->o_db);
            $o_group->setupElog($this->o_di);
            try {
                $a_groups = $o_group->read();
                foreach ($a_groups as $a_group) {
                    $a_auth_levels[strtolower($a_group['group_name'])] = $a_group['group_auth_level'];
                }
                if ($this->use_cache) {
                    $this->o_cache->set($group_cache_key, $a_auth_levels, 'groups');
                }
            }
            catch (ModelException $e) {
                $a_auth_levels = [];
            }
        }
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
            'auth_lvls'      => $a_auth_levels,
            'site_prefix'    => SITE_TWIG_PREFIX,
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
            'copyright_date' => COPYRIGHT_DATE,
            'debug_on'       => DEVELOPER_MODE ? 'true' : ''
        ];
        $a_values = array_merge($a_values, $a_page_values);
        return $a_values;
    }

    /**
     * Creates the template string used by TwigEnvironment::render.
     *
     * @param array $a_twig_values
     * @return string
     */
    public function createTplString(array $a_twig_values = []):string
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
     *
     * @param Di $o_di
     */
    protected function setOProperties(Di $o_di):void
    {
        if (null === $this->o_di) {
            $this->o_di = $o_di;
        }
        if (null === $this->o_router) {
            $this->o_router = $o_di->get('router');
        }
        if (null === $this->o_twig) {
            $this->o_twig = $o_di->get('twig');
        }
        if (null === $this->o_db) {
            $this->o_db = $o_di->get('db');
        }
        if (null === $this->o_session) {
            $this->o_session = $o_di->get('session');
        }
        if (null === $this->o_auth) {
            $this->o_auth = new AuthHelper($o_di);
        }
        if (null === $this->o_nav) {
            $this->o_nav = new NavComplexModel($this->o_di);
        }
        if (null === $this->o_routes_helper) {
            $this->o_routes_helper = new RoutesHelper($o_di);
        }
        if (USE_CACHE) {
            $o_cache = $o_di->get('cache');
            if (is_object($o_cache)) {
                $this->o_cache    = $o_cache;
                $this->cache_type = $this->o_cache->getCacheType();
                $this->use_cache  = empty($this->cache_type)
                    ? false
                    : true
                ;
            }
            else {
                $this->o_cache = null;
                $this->use_cache = false;
            }
        }
        if (defined('DEVELOPER_MODE') && DEVELOPER_MODE) {
            /** @var Elog $o_elog */
            $o_elog = $o_di->get('elog');
            $this->o_nav->setElog($o_elog);
        }
    }

    /**
     * Sets the class property $adm_level to a value of the highest auth level
     * found for login_id or 0 if not found.
     *
     * @param string $login_id Optional, if empty tries to use $_SESSION['login_id'].
     */
    protected function setAdmLevel($login_id = ''):void
    {
        $login_id = empty($login_id)
            ? empty($_SESSION['login_id'])
                ? 'empty'
                : $_SESSION['login_id']
            : $login_id;
        $cache_key = 'adm.level.for.' . $login_id;
        $adm_level = 0;
        if ($this->use_cache) {
            $adm_level = $this->o_cache->get($cache_key);
        }
        if (empty($adm_level)) {
            if ($login_id !== 'empty') {
                $adm_level = $this->o_auth->getHighestAuthLevel($login_id);
            }
            else {
                $adm_level = 0;
            }
        }
        if ($this->use_cache) {
            $this->o_cache->set($cache_key, $adm_level, 'adm');
        }
        $this->adm_level = $adm_level;
    }

    /**
     * Sets the class property a_nav.
     * Uses the retrieveNav method to do so.
     *
     * @param int|string $nav_group optional, defaults to 1
     */
    protected function setNav($nav_group = 1):void
    {
        $this->a_nav = $this->retrieveNav($nav_group);
    }

    /**
     * Sets the property a_nav array by the navgroup_name.
     *
     * @param string $navgroup_name
     */
    protected function setNavByNgName($navgroup_name = ''):void
    {
        $ng_id = -1;
        $cache_key = 'navgroup.id.by.' . $navgroup_name;
        if (USE_CACHE && is_object($this->o_cache)) {
            $ng_id = $this->o_cache->get($cache_key);
        }
        if ($ng_id < 1) {
            $o_ng = new NavgroupsModel($this->o_db);
            try {
                $ng_id = $o_ng->readIdByName($navgroup_name);
            }
            catch (ModelException $e) {
                $ng_id = 1;
            }
            if ($this->use_cache) {
                $this->o_cache->set($cache_key, $ng_id, 'navgroup');
            }
        }
        $this->setNav($ng_id);
    }

    /**
     * Creates and sets the o_twig property.
     * See the TwigFactory::getTwig method for details.
     *
     * @param string|array $twig_config \ref twigfactory
     * @param string       $name
     * @throws FactoryException
     */
    public function setTwig($twig_config = 'twig_config.php', $name = 'main'):void
    {
        try {
            $o_twig = TwigFactory::getTwig($twig_config, $name);
        }
        catch (FactoryException $e) {
            throw new FactoryException($e->errorMessage(), $e->getCode());
        }
        if (!$o_twig instanceof TwigEnvironment) {
            throw new FactoryException('Could not create twig object', ExceptionHelper::getCodeNumberFactory('start'));
        }
        $this->o_di->set('twig_' . $name, $o_twig);
        $this->o_twig = $o_twig;
    }

    /**
     * Retrieves the class property a_nav.
     *
     * @return array
     */
    public function getNav():array
    {
        return $this->a_nav;
    }

    /**
     * Returns an array with the values used primarily in the meta tags of the html.
     *
     * @param int|string $url_id
     * @return array
     */
    public function getPageValues($url_id = -1):array
    {
        if ((int) $url_id < 1) {
            $url_id = $this->urlId((string) $url_id);
        }
        try {
            $o_page_model = new PageComplexModel($this->o_di);
            $a_page_values = $o_page_model->readPageValuesByUrlId($url_id);
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
                'page_prefix'    => SITE_TWIG_PREFIX,
                'twig_dir'       => 'pages',
                'theme_name'     => 'base_fluid',
                'tpl'            => 'index',
                'a_content'      => [],
                'a_shared'       => [],
                'base_url'       => '/'
            ];
        }

        $base_url = $a_page_values['page_base_url'] === '/'
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
            'theme_name'  => $a_page_values['theme_name'],
            'tpl'         => $a_page_values['tpl_name'],
            'a_content'   => $a_page_values['a_content'],
            'a_shared'    => $this->getSharedValues(),
            'base_url'    => $base_url
        ];
    }

    /**
     * Returns the content shared between all pages.
     *
     * @return array
     */
    public function getSharedValues():array
    {
        try {
            $o_c = new ContentComplexModel($this->o_di);
            $results = $o_c->readCurrentShared();
            $a_shared_values = [];
            foreach ($results as $a_record) {
                $a_shared_values[$a_record['b_name']] = [
                    'c_content' => $a_record['c_content'],
                    'c_short_content' => $a_record['c_short_content'],
                    'c_type'          => $a_record['c_type']
                ];
            }
            return $a_shared_values;
        }
        catch (ModelException $e) {
            return [];
        }
    }

    ### Utilities ###
    /**
     * Utility to create a new unique integer value for the array passed in.
     * Recursive in nature, will find an integer value not in the array and return it.
     *
     * @param array $a_used
     * @param int   $value
     * @return int
     */
    protected function createNewOrderNumber(array $a_used = [], $value = 0):int
    {
        if (!in_array($value, $a_used, false)) {
            return (int) $value;
        }
        if (!in_array($value + 1, $a_used, false)) {
            return $value + 1;
        }
        return $this->createNewOrderNumber($a_used, $value + 1);
    }

    /**
     * Creates the values needed for the pager template.
     *
     * @param array $a_parameters Required in part```
     *                            [
     *                              'start_record'       => required
     *                              'records_to_display' => required
     *                              'total_records'      => required
     *                              'href'               => required
     *                              'get_params'         => optional - adds get params to the href
     *                              'use_to_display'     => optional - adds the number to display to the href
     *                              'use_page_numbers'   => optional - defaults to true, else uses record numbers
     *                            ]```
     * @return array
     */
    protected function createNumericPagerValues(array $a_parameters = []):array
    {
        $a_pager = [];
        $pager_key = 'pager.values.for.' . md5(json_encode($a_parameters));
        if (!isset($a_parameters['start_record'],
                   $a_parameters['records_to_display'],
                   $a_parameters['total_records'],
                   $a_parameters['href']
           )
        ) {
            if ($this->use_cache) {
                $this->o_cache->clearKey($pager_key);
            }
            return [];
        }
        if ($this->use_cache) {
            $a_pager = $this->o_cache->get($pager_key);
        }
        if (!empty($a_pager)) {
            return $a_pager;
        }
        $start_record       = (int)$a_parameters['start_record'];
        $records_to_display = (int)$a_parameters['records_to_display'];
        $total_records      = (int)$a_parameters['total_records'];
        $href               = (string)$a_parameters['href'] === '' ? '/' : (string)$a_parameters['href'];
        if ($records_to_display >= $total_records) {
            if ($this->use_cache) {
                $this->o_cache->set($pager_key, [], 'pager');
            }
            return [];
        }
        $get_stuff = '';
        if (!empty($a_parameters['get_params'])) {
            foreach($a_parameters['get_params'] as $key => $value) {
                $get_stuff .= $get_stuff === ''
                    ? '?' . $key . '=' . $value
                    : '&' . $key . '=' . $value;

            }
        }
        $use_to_display = '';
        if (isset($a_parameters['use_to_display']) && $a_parameters['use_to_display']) {
            $use_to_display = '/' . $records_to_display;
        }
        $use_page_numbers   = !empty($a_parameters['use_page_numbers'])
            ? $a_parameters['use_page_numbers']
            : true;
        $previous_value     = $start_record === 1 || $start_record - $records_to_display < 1
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
        $a_pager['first']    = $href
                               . '/1'
                               . $use_to_display
                               . '/'
                               . $get_stuff;
        $a_pager['previous'] = $href
                               . '/' . $previous_value
                               . $use_to_display
                               . '/' . $get_stuff;
        $a_pager['next']     = $href
                               . '/' . $next_value
                               . $use_to_display
                               . '/' . $get_stuff;
        $a_pager['links']    = [];
        $a_pager['last']     = $href
                               . '/' . (($number_of_pages - 1) * $records_to_display)
                               . $use_to_display
                               . '/' . $get_stuff;
        $display_links = !empty($a_parameters['display_links'])
            ? $a_parameters['display_links'] === 'all'
                ? $number_of_pages
                : $a_parameters['display_links']
            : 11;
        if ((int)$this_page === (int)$i_start) {
            $a_pager['previous'] = '';
            $a_pager['first'] = '';
        }
        if ($start_record === $records_to_display) {
            $a_pager['previous'] = $href
                                   . '/1'
                                   . $use_to_display
                                   . '/' . $get_stuff;
        }
        $i_end = $i_start + $display_links;
        if ($i_end > $number_of_pages) {
            $i_start = $number_of_pages - 10 < 1
                ? 1
                : $number_of_pages - 10;
            $i_end = $number_of_pages;
            if ((int)$i_end === (int)$this_page) {
                $a_pager['next'] = '';
                $a_pager['last'] = '';
            }
        }
        for ($i = $i_start; $i <= $i_end; $i++) {
            if ($use_page_numbers) {
                $text = $i;
            }
            elseif ($i === 1) {
                $text = 1;
            }
            else {
                $text = ($i - 1) * $records_to_display;
            }
            if ((int)$this_page === (int)$i) {
                $link = '';
            }
            elseif ($i === 1) {
                $link = $href
                        . '/' . $i
                        . $use_to_display
                        . '/' . $get_stuff;
            }
            else {
                $link = $href
                        . '/'
                        . (($i - 1) * $records_to_display)
                        . $use_to_display
                        . '/' . $get_stuff;
            }
            $a_pager['links'][] = [
                'href' => $link,
                'text' => $text
            ];
        }
        if ($this->use_cache) {
            $this->o_cache->set($pager_key, $a_pager, 'pager');
        }
        return $a_pager;
    }

    /**
     * Turns a flat db result into a multi-dimensional array for navigation.
     *
     * @param array $a_nav
     * @return array
     */
    protected function createSubmenu(array $a_nav = []):array
    {
        if ($a_nav === []) {
            return [];
        }
        $a_new_nav = [];
        foreach ($a_nav as $a_link) {
            if ((int)$a_link['nav_level'] === 1) {
                $this_link_id = $a_link['nav_id'] ?? 0;
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
     *
     * @param string|int $navgroup Defaults to the default navgroup.
     *                             This can be either the navgroup id or name.
     * @return array
     */
    protected function readNav($navgroup = ''):array
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
     *
     * @param array $a_nav If empty, this is a waste.
     * @return array
     */
    protected function removeUnauthorizedLinks(array $a_nav = []):array
    {
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
     *
     * @param array $a_nav
     * @return array
     */
    protected function sortTopLevel(array $a_nav = []):array
    {
        // first see if the links have duplicate sort order
        $a_used_order = [];
        $a_new_nav = [];
        foreach ($a_nav as $key => $a_link) {
            $order_number = !empty($a_link['nav_order'])
                ? (int)$a_link['nav_order']
                : 99;
            if (in_array($order_number, $a_used_order, false)) {
                $new_order_number = $this->createNewOrderNumber($a_used_order, $order_number);
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
     *
     * @param array $a_nav
     * @return array
     */
    protected function specifyActiveMenu(array $a_nav = []):array
    {
        $a_route_parts = $this->o_router->getRouteParts();
        $current_uri = $a_route_parts['request_uri'];
        foreach ($a_nav as $key => $a_item) {
            if (count($a_item['submenu']) > 0) {
                $a_nav[$key]['submenu'] = $this->specifyActiveMenu($a_item['submenu']);
            }
            else if ($a_item['url'] === $current_uri) {
                $a_item['class'] .= ' menu-active';
            }
            else {
                $a_item['class'] .= ' menu-inactive';
            }
        }
        return $a_nav;
    }

    /**
     * Makes sure the url_id is the actual record id.
     * If a string is given, looks up the url for the string.
     * Returns the record id for the url.
     *
     * @param string $url_string Optional, if not provided will return the
     *                           url id as specified by o_router.
     * @return int
     */
    public function urlId($url_string = ''):int
    {
        if ((string) $url_string === '-1') {
            $url_string = '';
        }
        $url_id = $this->o_router->getUrlId();
        if ($url_string === '' && $url_id > 0) {
            return $url_id;
        }
        if ($url_id > 0 && $url_string === $this->o_router->getRequestUri()) {
            return $url_id;
        }
        $o_url = new UrlsModel($this->o_db);
        try {
            $a_urls = $o_url->read(['url_text' => $url_string]);
            $url_id = empty($a_urls[0]['url_id'])
                ? $this->o_router->getUrlId()
                : $a_urls[0]['url_id'];
        }
        catch (ModelException $e) {
            $url_id = $this->o_router->getUrlId();
        }
        return $url_id;
    }
}
