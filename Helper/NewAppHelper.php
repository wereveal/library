<?php
/**
 * @brief     Helper for setting up a new app..
 * @details   Primarily creates new directories and files for the app.
 * @ingroup   lib_helper
 * @file      Ritc/Library/Helper/NewAppHelper.php
 * @namespace Ritc\Library\Helper
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.0
 * @date      2017-11-24 17:21:43
 * @note Change Log
 * - v1.0.0-alpha.0 - Initial version        - 2017-11-24 wer
 * @todo Ritc/Library/Helper/NewAppHelper.php - Everything
 */
namespace Ritc\Library\Helper;

use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class NewAppHelper.
 * @class   NewAppHelper
 * @package Ritc\Library\Helper
 */
class NewAppHelper
{
    use LogitTraits;

    /** @var array  */
    private $a_config;
    /** @var  array */
    private $a_new_dirs;
    /** @var  string */
    private $app_path;
    /** @var  string */
    private $htaccess_text;
    /** @var  string */
    private $keep_me_text;
    /** @var  string */
    private $tpl_text;

    public function __construct(Di $o_di)
    {
        $this->a_config = $o_di->getVar('a_install_config');
        $this->setupProperties();
    }

    /**
     * Creates directories for the new app.
     * @return bool
     */
    public function createDirectories()
    {
        if (!file_exists($this->app_path)) {
            if (mkdir($this->app_path, 0755, true) === false) {
                return false;
            }
        }
        foreach ($this->a_new_dirs as $dir) {
            $new_dir = $this->app_path . '/' . $dir;
            if (!file_exists($new_dir)) {
                if (mkdir($new_dir, 0755, true) === false) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Creates the default files for the app.
     * @return bool
     */
    public function createDefaultFiles()
    {
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
                        else {
                            if (!file_put_contents($new_file, $this->keep_me_text)) {
                                return false;
                            }
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
                $this->a_config['author'],
                $this->a_config['short_author'],
                $this->a_config['email'],
                date('Y-m-d H:i:s'),
                date('Y-m-d'),
                $this->a_config['twig_prefix']
            ];

            ### Create the main controller for the app ###
            $a_replace[4] = 'Main';
            $a_replace[5] = file_get_contents(SRC_CONFIG_PATH . '/install_files/main_controller.snippet');
            $controller_text = file_get_contents(SRC_CONFIG_PATH . '/install_files/controller.php.txt');
            $controller_text = str_replace($a_find, $a_replace, $controller_text);
            if (!file_put_contents($this->app_path . "/Controllers/MainController.php", $controller_text)) {
                return false;
            }

            ### Create the home controller for the app ###
            $a_replace[4] = 'Home';
            $a_replace[5] = file_get_contents(SRC_CONFIG_PATH . '/install_files/home_controller.snippet');
            $controller_text = file_get_contents(SRC_CONFIG_PATH . '/install_files/controller.php.txt');
            if ($controller_text) {
                $controller_text = str_replace($a_find, $a_replace, $controller_text);
                if (!file_put_contents($this->app_path . "/Controllers/HomeController.php", $controller_text)) {
                    return false;
                }
            }
            else {
                return false;
            }

            ### Create the manager controller for the app ###
            $a_replace[4] = 'Manager';
            $a_replace[5] = '';
            $controller_text = file_get_contents(SRC_CONFIG_PATH . '/install_files/ManagerController.php.txt');
            if ($controller_text) {
                $controller_text = str_replace($a_find, $a_replace, $controller_text);
                if (!file_put_contents($this->app_path . "/Controllers/ManagerController.php", $controller_text)) {
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
                if (!file_put_contents($this->app_path . "/Views/HomeView.php", $view_text)) {
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
                if (!file_put_contents($this->app_path . "/Views/ManagerView.php", $view_text)) {
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
            $base_twig = '/templates/default/base.twig';
            $resource_path = $this->app_path . '/resources';
            $twig_text = file_get_contents(SRC_PATH . $base_twig);
            if ($twig_text) {
                if (!file_put_contents($resource_path . $base_twig, $twig_text)) {
                    return false;
                }
            }
            $default_templates_path = SRC_PATH . '/templates/pages/';
            $a_default_files = scandir($default_templates_path);
            $pages_path = $resource_path . '/templates/pages/';
            foreach ($a_default_files as $this_file) {
                if ($this_file != '.' && $this_file != '..') {
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
            $developer_mode = $this->a_config['developer_mode'] == 'true'
                ? 'true'
                : 'false';
            $http_host = $this->a_config['http_host'];
            $domain = empty($this->a_config['domain'])
                ? ''
                : $this->a_config['domain'];
            $tld = empty($this->a_config['tld'])
                ? 'com'
                : $this->a_config['tld'];

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
                ''
            ];
            if (!empty($http_host)) {
                $host_text = file_get_contents(SRC_CONFIG_PATH . '/install_files/specific_host.snippet');
                $host_text = str_replace($a_find, $a_replace, $host_text);
                $a_replace[7] = $host_text;
            }
            $setup_text = file_get_contents(SRC_CONFIG_PATH . '/install_files/setup.php.txt');
            $setup_text = str_replace($a_find, $a_replace, $setup_text);
            file_put_contents(PUBLIC_PATH . '/setup.php', $setup_text);

            return true;
        }
        return false;
    }

    /**
     * Sets the class properties that are needed.
     */
    private function setupProperties()
    {
        $this->app_path = APPS_PATH
                          . '/'
                          . $this->a_config['namespace']
                          . '/'
                          . $this->a_config['app_name'];
        $this->a_new_dirs = [
            'Abstracts',
            'Controllers',
            'Entities',
            'Interfaces',
            'Models',
            'Tests',
            'Traits',
            'Views',
            'resources',
            'resources/config',
            'resources/sql',
            'resources/templates',
            'resources/templates/default',
            'resources/templates/elements',
            'resources/templates/pages',
            'resources/templates/forms',
            'resources/templates/snippets',
            'resources/templates/tests'
        ];
        $this->htaccess_text =<<<EOF
<IfModule mod_authz_core.c>
    Require all denied
</IfModule>
<IfModule !mod_authz_core.c>
    Order deny,allow
    Deny from all
</IfModule>
EOF;
        $this->keep_me_text = "Place Holder";
        $this->tpl_text = "<h3>An Error Has Occurred</h3>";
    }
}