<?php
/**
 * Class NewAppHelper
 * @package Ritc_Library
 */
namespace Ritc\Library\Helper;

use Ritc\Library\Exceptions\CustomException;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Models\GroupsModel;
use Ritc\Library\Models\PeopleComplexModel;
use Ritc\Library\Models\TwigComplexModel;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;

/**
 * Helper for setting up a new app.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.1.0
 * @date    2018-05-29 19:11:01
 * @change_log
 * - v1.1.1         - Bug fixes                                                     - 2018-05-29 wer
 * - v1.1.0         - Create Default Files changed to use over all site variable    - 2018-04-14 wer
 * - v1.0.0         - Initial Production version                                    - 2017-12-15 wer
 * - v1.0.0-alpha.0 - Initial version                                               - 2017-11-24 wer
 */
class NewAppHelper
{
    use LogitTraits;

    /** @var array $a_config */
    private $a_config;
    /** @var  array $a_new_dirs */
    private $a_new_dirs;
    /** @var  string $app_path */
    private $app_path;
    /** @var  string $htaccess_text */
    private $htaccess_text;
    /** @var  string $keep_me_text */
    private $keep_me_text;
    /** @var \Ritc\Library\Services\Di $o_di */
    private $o_di;
    /** @var  string $tpl_text */
    private $tpl_text;

    /**
     * NewAppHelper constructor.
     *
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->o_di = $o_di;
        $this->setConfig($o_di->getVar('a_install_config'));
        $this->setupProperties();
    }

    /**
     * Creates directories for the new app.
     *
     * @return bool
     */
    public function createDirectories():bool
    {
        if (!file_exists($this->app_path) &&
            !mkdir($this->app_path, 0755, true) &&
            !is_dir($this->app_path)
        ) {
            return false;
        }
        foreach ($this->a_new_dirs as $dir) {
            $new_dir = $this->app_path . '/' . $dir;
            if (!file_exists($new_dir) &&
                !mkdir($new_dir, 0755, true) &&
                !is_dir($new_dir)
            ) {
                return false;
            }
        }
        return true;
    }

    /**
     * Creates the default files for the app.
     *
     * @param bool $is_site optional, defaults to false
     * @return bool
     */
    public function createDefaultFiles($is_site = false):bool
    {
        if (empty($this->a_new_dirs)
            || empty($this->app_path)
            || empty($this->htaccess_text)
            || empty($this->tpl_text)
            || empty($this->a_config)
            || empty($this->a_config['namespace'])
            || empty($this->a_config['app_name'])
        ) {
            return false;
        }
        if (!isset($this->a_config['author'])) {
            $this->a_config['author'] = '';
        }
        if (!isset($this->a_config['short_author'])) {
            $this->a_config['short_author'] = '';
        }
        if (!isset($this->a_config['email'])) {
            $this->a_config['email'] = '';
        }
        if (!isset($this->a_config['app_twig_prefix'])) {
            $this->a_config['app_twig_prefix'] = 'main_';
        }
        if (file_exists($this->app_path)) {
            if (file_put_contents($this->app_path . '/.htaccess', $this->htaccess_text)) {
                foreach ($this->a_new_dirs as $dir) {
                    $dir = $this->app_path . '/' . $dir;
                    $new_file = $dir . '/.keepme';
                    $new_tpl  = $dir . '/no_file.twig';
                    if (file_exists($dir)) {
                        if (strpos($dir, 'templates') !== false) {
                            if (!file_put_contents($new_tpl, $this->tpl_text)) {
                                return false;
                            }
                        }
                        else if (!file_put_contents($new_file, $this->keep_me_text)) {
                            return false;
                        }
                    }
                    else {
                        return false;
                    }
                }
            }
            $a_find = [
                '{NAMESPACE}',
                '{APPNAME}',
                '{namespace}',
                '{app_name}',
                '{controller_name}',
                '{controller_method}',
                '{controller_use}',
                '{controller_vars}',
                '{controller_construct}',
                '{author}',
                '{sauthor}',
                '{email}',
                '{idate}',
                '{sdate}',
                '{twig_prefix}'
            ];
            $a_replace = [
                $this->a_config['namespace'],
                $this->a_config['app_name'],
                strtolower($this->a_config['namespace']),
                strtolower($this->a_config['app_name']),
                '',
                '',
                '',
                '',
                '',
                $this->a_config['author'],
                $this->a_config['short_author'],
                $this->a_config['email'],
                date('Y-m-d H:i:s'),
                date('Y-m-d'),
                $this->a_config['app_twig_prefix']
            ];

            ### Create the main controller for the app ###
            if ($is_site) {
                $controller_text = file_get_contents(SRC_CONFIG_PATH . '/install_files/MasterController.php.txt');
                $controller_text = str_replace($a_find, $a_replace, $controller_text);
                if (!file_put_contents($this->app_path . '/Controllers/MasterController.php', $controller_text)) {
                    return false;
                }
            }
            else {
                $a_replace[4] = 'Main';
                $a_replace[5] = file_get_contents(SRC_CONFIG_PATH . '/install_files/main_controller.snippet');
                $controller_text = file_get_contents(SRC_CONFIG_PATH . '/install_files/controller.php.txt');
                $controller_text = str_replace($a_find, $a_replace, $controller_text);
                if (!file_put_contents($this->app_path . '/Controllers/MainController.php', $controller_text)) {
                    return false;
                }
            }

            ### Create the home controller for the app ###
            $controller_text = file_get_contents(SRC_CONFIG_PATH . '/install_files/HomeController.php.txt');
            if ($controller_text) {
                $controller_text = str_replace($a_find, $a_replace, $controller_text);
                if (!file_put_contents($this->app_path . '/Controllers/HomeController.php', $controller_text)) {
                    return false;
                }
            }
            else {
                return false;
            }

            ### Create the manager controller for the app ###
            $controller_text = file_get_contents(SRC_CONFIG_PATH . '/install_files/ManagerController.php.txt');
            if ($controller_text) {
                $controller_text = str_replace($a_find, $a_replace, $controller_text);
                if (!file_put_contents($this->app_path . '/Controllers/ManagerController.php', $controller_text)) {
                    return false;
                }
            }
            else {
                return false;
            }

            ### Create the home view for the app ###
            $view_text = file_get_contents(SRC_CONFIG_PATH . '/install_files/HomeView.php.txt');
            if ($view_text) {
                $view_text = str_replace($a_find, $a_replace, $view_text);
                if (!file_put_contents($this->app_path . '/Views/HomeView.php', $view_text)) {
                    return false;
                }
            }
            else {
                return false;
            }

            ### Create the manager view for the app ###
            $view_text = file_get_contents(SRC_CONFIG_PATH . '/install_files/ManagerView.php.txt');
            if ($view_text) {
                $view_text = str_replace($a_find, $a_replace, $view_text);
                if (!file_put_contents($this->app_path . '/Views/ManagerView.php', $view_text)) {
                    return false;
                }
            }
            else {
                return false;
            }

            ### Create the doxygen config for the app ###
            $doxy_text = file_get_contents(SRC_CONFIG_PATH . '/install_files/doxygen_config.php.txt');
            if ($doxy_text) {
                $doxy_text = str_replace($a_find, $a_replace, $doxy_text);
                if (!file_put_contents($this->app_path . '/resources/config/doxygen_config.php', $doxy_text)) {
                    return false;
                }
            }
            else {
                return false;
            }

            ### Create the twig_config file ###
            $twig_file = file_get_contents(SRC_CONFIG_PATH . '/install_files/twig_config.php.txt');
            if ($twig_file) {
                $new_twig_file = str_replace($a_find, $a_replace, $twig_file);
                if (!file_put_contents($this->app_path . '/resources/config/twig_config.php', $new_twig_file)) {
                    return false;
                }
            }
            else {
                return false;
            }

            ### Copy two main twig files ###
            $base_twig = '/templates/themes/base.twig';
            $resource_path = $this->app_path . '/resources';
            $twig_text = file_get_contents(SRC_PATH . $base_twig);
            if ($twig_text && !file_put_contents($resource_path . $base_twig, $twig_text)) {
                return false;
            }
            $default_templates_path = SRC_PATH . '/templates/pages/';
            $a_default_files = scandir($default_templates_path, SCANDIR_SORT_ASCENDING);
            $pages_path = $resource_path . '/templates/pages/';
            foreach ($a_default_files as $this_file) {
                if ($this_file !== '.' && $this_file !== '..') {
                    $twig_text = file_get_contents($default_templates_path . $this_file);
                    if ($twig_text) {
                        if (!file_put_contents($pages_path . $this_file, $twig_text)) {
                            return false;
                        }
                    }
                    else {
                        return false;
                    }
                }
            }
            if ($is_site) {
                $a_find = [
                    '{NAMESPACE}',
                    '{APPNAME}'
                ];
                $a_replace = [
                    $this->a_config['namespace'],
                    $this->a_config['app_name']
                ];
                $index_text = file_get_contents(SRC_CONFIG_PATH . '/install_files/index.php.txt');
                if ($index_text) {
                    $index_text = str_replace($a_find, $a_replace, $index_text);
                    if (!file_put_contents(PUBLIC_PATH . '/index.php', $index_text)) {
                        return false;
                    }
                }
                $public_path = empty($this->a_config['public_path'])
                    ? '$_SERVER["DOCUMENT_ROOT"]'
                    : $this->a_config['public_path'];
                $base_path = empty($this->a_config['base_path'])
                    ? 'dirname(PUBLIC_PATH)'
                    : $this->a_config['base_path'];
                $developer_mode = isset($this->a_config['developer_mode']) && $this->a_config['developer_mode'] === 'true'
                    ? 'true'
                    : 'false';
                $http_host = empty($this->a_config['http_host'])
                    ? ''
                    : $this->a_config['http_host'];
                $domain = empty($this->a_config['domain'])
                    ? ''
                    : $this->a_config['domain'];
                $tld = empty($this->a_config['tld'])
                    ? 'com'
                    : $this->a_config['tld'];
                $specific_host = empty($this->a_config['specific_host'])
                    ? ''
                    : $this->a_config['specific_host'];
                $a_find = [
                    '{db_config_file}',
                    '{public_path}',
                    '{base_path}',
                    '{developer_mode}',
                    '{http_host}',
                    '{domain}',
                    '{tld}',
                    '{specific_host}'
                ];
                $a_replace = [
                    $this->a_config['db_file'],
                    $public_path,
                    $base_path,
                    $developer_mode,
                    $http_host,
                    $domain,
                    $tld,
                    $specific_host
                ];
                if (!empty($http_host)) {
                    $host_text = file_get_contents(SRC_CONFIG_PATH . '/install_files/http_host.snippet');
                    $host_text = str_replace($a_find, $a_replace, $host_text);
                    $a_replace[7] = $host_text;
                }
                $setup_text = file_get_contents(SRC_CONFIG_PATH . '/install_files/setup.php.txt');
                $setup_text = str_replace($a_find, $a_replace, $setup_text);
                file_put_contents(PUBLIC_PATH . '/setup.php', $setup_text);
            }
            return true;
        }
        return false;
    }

    /**
     * @return array|string
     */
    public function createTwigDbRecords()
    {
        $o_tcm = new TwigComplexModel($this->o_di);
        $app_resource_dir = str_replace(BASE_PATH, '', $this->app_path) . '/resources/templates';
        $a_values = [
           'tp_prefix'  => $this->a_config['app_twig_prefix'],
           'tp_path'    => $app_resource_dir,
           'tp_active'  => 'true',
           'tp_default' => $this->a_config['master_twig']
        ];
        try {
            return $o_tcm->createTwigForApp($a_values);
        }
        catch (ModelException $e) {
            return $e->errorMessage();
        }
    }

    /**
     * Creates Users and possibly groups from the the data.
     *
     * @return array
     */
    public function createUsers(): array
    {
        try {
            $o_people = new PeopleComplexModel($this->o_di);
        }
        catch (CustomException $e) {
            return ViewHelper::errorMessage($e->getMessage());
        }
        /** @var DbModel $o_db */
        $o_db = $this->o_di->get('db');
        $o_groups = new GroupsModel($o_db);
        $o_groups->setupElog($this->o_di);

        $a_people = empty($this->a_config['a_users'])
            ? []
            : $this->a_config['a_users'];
        $a_groups = empty($this->a_config['a_groups'])
            ? []
            : $this->a_config['a_groups'];
        if (empty($a_people) && empty($a_groups)) {
            return ViewHelper::infoMessage('Nothing to save');
        }
        if (!empty($a_groups)) {
            try {
                $a_found_groups = $o_groups->read();
            }
            catch (ModelException $e) {
                return ViewHelper::errorMessage('A problem occurred retrieving groups');
            }
            foreach ($a_found_groups as $key => $a_found_group) {
                $value = $a_found_group['group_name'];
                $found_key = Arrays::inArrayRecursive($value, $a_groups, true);
                if (!$found_key) {
                    $found_key = $found_key[0];
                    unset($a_groups[$found_key]);
                }
            }
            try {
                $o_groups->create($a_groups);
            }
            catch (ModelException $e) {
                return ViewHelper::errorMessage('Could not save the groups: ' . $e->getMessage());
            }
        }
        if (!empty($a_people)) {
            foreach ($a_people as $person_key => $a_person) {
                try {
                    $a_groups = $o_groups->readByName($a_person['group_name']);
                    unset($a_person['group_name']);
                    $a_person['groups'] = [$a_groups[0]['group_id']];
                    $o_people->savePerson($a_person);
                }
                catch (ModelException $e) {
                    return ViewHelper::errorMessage('A problem occurred trying to save the person: ' . $e->errorMessage());
                }
            }
            return ViewHelper::successMessage();
        }
        return ViewHelper::errorMessage('Unknown error occurred.');
    }

    /**
     * Standard class property SETter, app_path.
     *
     * @param string $value
     */
    public function setAppPath($value = ''):void
    {
        if ($value === '') {
            $value = APPS_PATH
                   . '/'
                   . $this->a_config['namespace']
                   . '/'
                   . $this->a_config['app_name'];
        }
        $this->app_path = $value;
    }

    /**
     * Standard class property SETter for a_values.
     *
     * @param array $a_values Optional, defaults to a preset bunch of values.
     */
    public function setConfig(array $a_values = []):void
    {
        $a_default = [
            'app_name'        => 'Main',                          // specify the primary app to which generates the home page
            'namespace'       => 'Ritc',                          // specify the root namespace the app will be in
            'author'          => 'William E Reveal',              // specify the author of the app
            'short_author'    => 'wer',                           // abbreviation for the author
            'email'           => '<bill@revealitconsulting.com>', // email of the author
            'public_path'     => '',                              // leave blank for default setting
            'base_path'       => '',                              // leave blank for default setting
            'http_host'       => '',                              // $_SERVER['HTTP_HOST'] results or leave blank for default
            'domain'          => 'revealitconsulting',            // domain name of site
            'tld'             => 'com',                           // top level domain, e.g., com, net, org
            'specific_host'   => '',                              // e.g. www, test
            'developer_mode'  => 'false',                         // affects debugging messages
            'master_app'      => 'false',                         // specifies if this app is the one called by /index.php and specifies the MasterController
            'master_twig'     => 'false'                          // specifies if this app's twig prefix is the default (if false, site_ is default)
        ];

        if (empty($a_values)) {
            $a_values = $a_default;
        }
        foreach ($a_default as $key => $value) {
            if (!isset($a_values[$key])) {
                $a_values[$key] = $a_default[$key];
            }
        }
        if (empty($a_values['app_twig_prefix'])) {
            $a_values['app_twig_prefix'] = strtolower($a_values['app_name']) . '_';
        }
        $this->a_config = $a_values;
    }

    /**
     * Standard class property SETter, htaccess_text.
     * @param string $value
     */
    public function setHtaccessText($value = ''):void
    {
        if ($value === '') {
            $value =<<<EOF
<IfModule mod_authz_core.c>
    Require all denied
</IfModule>
<IfModule !mod_authz_core.c>
    Order deny,allow
    Deny from all
</IfModule>
EOF;
        }
        $this->htaccess_text = $value;
    }

    /**
     * Standard class property SETter, keepme_text.
     * @param string $value
     */
    public function setKeepMeText($value = ''):void
    {
        if ($value === '') {
            $value = 'Place Holder';
        }
        $this->keep_me_text = $value;
    }

    /**
     * Standard class property SETter, a_new_dirs.
     * @param array $a_values
     */
    public function setNewDirs(array $a_values = []):void
    {
        if (empty($a_values)) {
            $a_values = [
                'Abstracts',
                'Controllers',
                'Entities',
                'Interfaces',
                'Models',
                'Tests',
                'Traits',
                'Views',
                'resources',
                'resources/assets',
                'resources/assets/images',
                'resources/assets/js',
                'resources/assets/scss',
                'resources/assets/txt',
                'resources/config',
                'resources/sql',
                'resources/templates',
                'resources/templates/elements',
                'resources/templates/pages',
                'resources/templates/forms',
                'resources/templates/snippets',
                'resources/templates/tests',
                'resources/templates/themes'
            ];
        }
        $this->a_new_dirs = $a_values;
    }

    /**
     * Standard class property SETter, tpl_text.
     * @param string $value
     */
    public function setTplText($value = ''):void
    {
        if ($value === '') {
            $value = '<h3>An Error Has Occurred</h3>';
        }
        $this->tpl_text = $value;
    }

    /**
     * Sets the class properties that are needed.
     */
    private function setupProperties():void
    {

        $this->setAppPath();
        $this->setNewDirs();
        $this->setHtaccessText();
        $this->setKeepMeText();
        $this->setTplText();
    }
}
