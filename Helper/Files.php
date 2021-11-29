<?php /** @noinspection PhpUndefinedConstantInspection */
/**
 * Class Files
 *
 * @package Ritc_Library
 */
namespace Ritc\Library\Helper;

use Ritc\Library\Interfaces\LocationInterface;
use Ritc\Library\Traits\LogitTraits;

/**
 * Determines the path to the file
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v4.4.0
 * @date    2021-11-29 15:51:06
 * @note The constants with _DIR_NAME should correspond to dir names in
 *       the site theme or namespace (e.g. templates are in namespace). If a directory
 *       is missing, this could cause a fatal error.
 * @change_log
 * - v4.4.0 - updated for php8                                                      - 2021-11-29 wer
 * - v4.3.1 - fixed bug (long standing)                                             - 2016-04-21 wer
 * - v4.3.0 - added an addition possible file location                              - 2016-04-20 wer
 * - v4.2.1 - moved to the namespace Ritc\Library\Helper                            - 11/15/2014 wer
 * - v4.2.1 - implements changes to Base class for logging
 * - v4.2.0 - Several changes.                                                      - 12/19/2013 wer
 *            Base was changed from abstract to normal class
 *            Location was modified to be an interface
 *            Two methods needed added to match the Location interface.
 *            Unused stuff removed.
 * - v4.1.2 - some layout changes require changes in code.                          - 07/06/2013 wer
 *            NOTE: the fact that layout changes require this class to be changed
 *            is not good. I need to change it so that the parameters.php file
 *            can specify the layout. But that won't be trivial.
 * - v4.1.1 - bug fixes and clean up                                                - 4/30/2013 wer
 * - v4.1.0 - New RITC Library Layout serious changes wer
 *            BUT method results and names were not changed
 * - v4.0.0 - FIG standards (mostly) wer
 */
class Files implements LocationInterface
{
    use LogitTraits;

    /**
     * Name of the config dir.
     *
     * @var 'CONFIG_DIR_NAME'
     */
    public const CONFIG_DIR_NAME = 'config';
    /**
     * Name of the css dir.
     *
     * @var 'CSS_DIR_NAME'
     */
    public const CSS_DIR_NAME = 'css';
    /**
     * Name of the HTML dir.
     *
     * @var 'HTML_DIR_NAME' a class const
     */
    public const HTML_DIR_NAME = 'html';
    /**
     * Name of the Images dir.
     *
     * @var 'IMAGES_DIR_NAME'
     */
    public const IMAGES_DIR_NAME = 'images';
    /**
     * Name of the javascript directory.
     *
     * @var 'JS_DIR_NAME'
     */
    public const JS_DIR_NAME = 'js';
    /**
     * Name of the Library dir. Not sure which dir this is actually referring to.
     *
     * @var 'LIBS_DIR_NAME'
     */
    public const LIBS_DIR_NAME = 'library';
    /**
     * Name of the directory for private files.
     *
     * @var 'PRIVATE_DIR_NAME'
     */
    public const PRIVATE_DIR_NAME = 'private';
    /**
     * Name of the directory for templates.
     *
     * @var 'TEMPLATES_DIR_NAME'
     */
    public const TEMPLATES_DIR_NAME = 'templates';
    /**
     * Name of the directory for temporary files.
     *
     * @var 'TMP_DIR_NAME'
     */
    public const TMP_DIR_NAME = 'tmp';
    /**
     * Name of the file to find.
     *
     * @var string
     */
    protected string $file_name = 'no_file.tpl';
    /**
     * Name of the directory the file is located.
     *
     * @var string
     */
    protected string $file_dir_name = '';
    /**
     * Namespace the file is located in.
     *
     * @var string
     */
    protected string $namespace;
    /**
     * File with the directory path from base path.
     *
     * @var string
     */
    protected string $file_w_dir;
    /**
     * File with the path from the root of the server.
     *
     * @var string
     */
    protected string $file_w_path;
    /**
     * Name of the theme.
     *
     * @var string
     */
    protected string $theme_name = '';

    /**
     * Files constructor.
     *
     * @param string $file_name
     * @param string $the_directory
     * @param string $theme_name
     * @param string $namespace
     */
    public function __construct(string $file_name = '', string $the_directory = '', string $theme_name = '', string $namespace = '')
    {
        if ($file_name !== '') {
            $this->file_name = $file_name;
        }
        if ($the_directory !== '') {
            $this->file_dir_name = $the_directory;
        }
        if ($theme_name !== '') {
            $this->theme_name = $theme_name;
        }
        if ($namespace !== '') {
            $this->namespace = $namespace;
        } else {
            $this->namespace = __NAMESPACE__;
        }
        $this->setFileLocations();
    }

    /**
     * @param $var_name
     * @return mixed
     */
    public function getVar($var_name): mixed
    {
        return $this->$var_name;
    }

    /**
     * @param $file_name
     * @return bool
     */
    public function getConfigWithDir($file_name):bool
    {
        $this->file_dir_name = defined('CONFIG_DIR_NAME')
            ? CONFIG_DIR_NAME
            : self::CONFIG_DIR_NAME;
        return $this->getFileWithDir($file_name);
    }

    /**
     * @param $file_name
     * @return bool
     */
    public function getConfigWithPath($file_name):bool
    {
        $this->file_dir_name = defined('CONFIG_DIR_NAME')
            ? CONFIG_DIR_NAME
            : self::CONFIG_DIR_NAME;
        return $this->getFileWithPath($file_name);
    }

    /**
     * Returns the contents of a file as a string.
     * Does more than the php function of file_get_contents in that
     * file_get_contents requires a full path where as this only requires
     * a file name. The path can be specified either directly or indirectly.
     *
     * @note Examples:
     *     getContents('test.tpl');\n
     *     getContents('test.css', 'css');\n
     *     getContents('test/test.tpl', 'templates');\n
     *     getContents('fred.html', 'my/file/path');
     * @param string $file_name     (str) - name of file - required - can include
     *     sub-paths to work with one of the several file_dir_name types,
     *     especially useful when using sub-dirs of templates and other
     *     sections of the theme.
     * @param string $file_dir_name (str) - one of several types or raw path - optional
     * @return string - the contents of the file or false.
     */
    public function getContents(string $file_name = '', string $file_dir_name = ''):string
    {
        if($file_name === '' && $this->file_name === 'no_file.tpl') {
            $this->logIt('File name was blank.', LOG_OFF, __METHOD__);
            return false;
        }
        if ($file_dir_name !== '') {
            $this->file_dir_name = $file_dir_name;
            if ($file_name === '') {
                /*
                    the file name has been set at a different time but
                    the file locations have been changed based on $file_dir_name
                    so the file locations have to be set again.
                */
                $this->setFileLocations();
            }
        }
        $file_path = $this->getFileWithPath($file_name);
        $file_contents = !empty($file_path) ? file_get_contents($file_path) : false ;
        if($file_contents !== false) {
            return $file_contents;
        }

        $this->logIt('Could not get File Contents. File Path: ' . $file_path, LOG_OFF, __METHOD__);
        return false;
    }

    /**
     * @param $file_name
     * @return bool
     */
    public function getCssWithDir($file_name):bool
    {
        $this->file_dir_name = defined('CSS_DIR_NAME')
            ? CSS_DIR_NAME
            : self::CSS_DIR_NAME;
        return $this->getFileWithDir($file_name);
    }

    /**
     * @param string $file_name
     * @return bool
     */
    public function getCssWithPath(string $file_name = ''):bool
    {
        $this->file_dir_name = defined('CSS_DIR_NAME')
            ? CSS_DIR_NAME
            : self::CSS_DIR_NAME;
        return $this->getFileWithPath($file_name);
    }

    /**
     * @return string
     */
    public function getFileName():string
    {
        return $this->file_name;
    }

    /**
     * @return string
     */
    public function getFileDirName():string
    {
        return $this->file_dir_name;
    }

    /**
     * @param string $file_name
     * @return string
     */
    public function getFileWithDir(string $file_name = ''):string
    {
        if ($file_name !== '') {
            $this->setFileName($file_name);
        }
        if (file_exists($this->file_w_path) && !is_dir($this->file_w_path)) {
            return $this->file_w_dir;
        }

        $log_from = __METHOD__ . '.' . __LINE__;
        $this->logIt("Couldn't get file on the following paths: $this->file_w_path", LOG_OFF, $log_from);
        return '';
    }

    /**
     * @param string $file_name
     * @return string
     */
    public function getFileWithPath(string $file_name = ''):string
    {
        if ($file_name !== '') {
            $this->setFileName($file_name);
        }
        if (file_exists($this->file_w_path) && !is_dir($this->file_w_path)) {
            return $this->file_w_path;
        }

        $log_from = __METHOD__ . '.' . __LINE__;
        $this->logIt("The file doesn't exist on the following paths: {$this->file_w_path}", LOG_OFF, $log_from);
        return '';
    }

    /**
     * @param $file_name
     * @return bool
     */
    public function getHtmlWithDir($file_name):bool
    {
        $this->file_dir_name = defined('HTML_DIR_NAME')
            ? HTML_DIR_NAME
            : self::HTML_DIR_NAME;
        return $this->getFileWithDir($file_name);
    }

    /**
     * @param $file_name
     * @return bool
     */
    public function getHtmlWithPath($file_name):bool
    {
        $this->file_dir_name = defined('HTML_DIR_NAME')
            ? HTML_DIR_NAME
            : self::HTML_DIR_NAME;
        return $this->getFileWithPath($file_name);
    }

    /**
     * @param $file_name
     * @return bool
     */
    public function getImageWithDir($file_name):bool
    {
        $this->file_dir_name = defined('IMAGES_DIR_NAME')
            ? IMAGES_DIR_NAME
            : self::IMAGES_DIR_NAME;
        return $this->getFileWithDir($file_name);
    }

    /**
     * @param $file_name
     * @return bool
     */
    public function getImageWithPath($file_name):bool
    {
        $this->file_dir_name = defined('IMAGES_DIR_NAME')
            ? IMAGES_DIR_NAME
            : self::IMAGES_DIR_NAME;
        return $this->getFileWithPath($file_name);
    }

    /**
     * @param $file_name
     * @return bool
     */
    public function getJsWithDir($file_name):bool
    {
        $this->file_dir_name = defined('JS_DIR_NAME')
            ? JS_DIR_NAME
            : self::JS_DIR_NAME;
        $this->setFileLocations();
        return $this->getFileWithDir($file_name);
    }

    /**
     * @param $file_name
     * @return bool
     */
    public function getJsWithPath($file_name):bool
    {
        $this->file_dir_name = defined('JS_DIR_NAME')
            ? JS_DIR_NAME
            : self::JS_DIR_NAME;
        $this->setFileLocations();
        return $this->getFileWithPath($file_name);
    }

    /**
     * @param $file_name
     * @return bool
     */
    public function getLibWithDir($file_name):bool
    {
        $this->file_dir_name = defined('LIBS_DIR_NAME')
            ? LIBS_DIR_NAME
            : self::LIBS_DIR_NAME;
        return $this->getFileWithDir($file_name);
    }

    /**
     * @param $file_name
     * @return bool
     */
    public function getLibWithPath($file_name):bool
    {
        $this->file_dir_name = defined('LIBS_DIR_NAME')
            ? LIBS_DIR_NAME
            : self::LIBS_DIR_NAME;
        return $this->getFileWithPath($file_name);
    }

    /**
     * @param string $file_name
     * @return bool|string
     */
    public function getPrivateFile(string $file_name = ''): bool|string
    {
        $private_path = $_SERVER['DOCUMENT_ROOT'] . '/../' . self::PRIVATE_DIR_NAME;
        $file_w_path = $private_path . '/' . $file_name;
        if (file_exists($file_w_path)) {
            return $file_w_path;
        }
        $this->logIt("The File w/ Path didn't exist: '$file_w_path'. It is possible the Constant PRIVATE_PATH isn't set.", LOG_OFF, __METHOD__ . '.' . __LINE__);
        return false;
    }

    /**
     * @param string $file_name
     * @return bool|string
     */
    public function getTmpFile(string $file_name = ''): bool|string
    {
        $file_w_path = TMP_PATH . '/' . $file_name;
        if (file_exists($file_w_path)) {
            return $file_w_path;
        }
        return false;
    }

    /**
     * @param $file_name
     * @return bool
     */
    public function getTemplateWithDir($file_name):bool
    {
        $this->file_dir_name = defined('TEMPLATES_DIR_NAME')
            ? TEMPLATES_DIR_NAME
            : self::TEMPLATES_DIR_NAME;
        return $this->getFileWithDir($file_name);
    }

    /**
     * @param $file_name
     * @return bool
     */
    public function getTemplateWithPath($file_name):bool
    {
        $this->file_dir_name = defined('TEMPLATES_DIR_NAME')
            ? TEMPLATES_DIR_NAME
            : self::TEMPLATES_DIR_NAME;
        $this->setFileLocations();
        return $this->getFileWithPath($file_name);
    }

    /**
     * @param string $which_one
     * @return array
     */
    public function getFileLocations(string $which_one = ''):array
    {
        $a_file_locations = array();
        switch($which_one) {
            case 'file_w_dir':
                $a_file_locations['file_w_dir'] = $this->file_w_dir;
                break;
            case 'file_w_path':
                $a_file_locations['file_w_path'] = $this->file_w_path;
                break;
            default:
                $a_file_locations['file_w_dir']  = $this->file_w_dir;
                $a_file_locations['file_w_path'] = $this->file_w_path;
        }
        return $a_file_locations;
    }

    /**
     * Finds the location of the file
     * the possible places a file could exist.
     *   <pre>One of many places:
     *     NOTE: $file_dir_name can be pathlike, e.g. '/assets/css'
     *           $file_name can also be pathlike, e.g. '/assets/css/main.css'
     *     PUBLIC_PATH/$file_name
     *     PUBLIC_PATH/$file_dir_name/$file_name
     *     PUBLIC_PATH/assets/$file_name
     *     PUBLIC_PATH/assets/$file_dir_name/$file_name
     *     PUBLIC_PATH/assets/themes/$this->theme_name/$file_dir_name/$file_name
     *     BASE_PATH/$file_name
     *     BASE_PATH/$file_dir_name/$file_name
     *     SRC_PATH/$file_name
     *     SRC_PATH/config/$file_name
     *     SRC_PATH/$file_dir_name/$file_name
     *     SRC_PATH/str_replace('Ritc\', '', $namespace)/$file_name
         *
         * @param string $file_name     required
     * @param string     $namespace     optional defaults to $this->namespace
     * @param string     $file_dir_name optional default to none
     * @return string|array|bool        str $path_of_file or false
     */
    public function locateFile(string $file_name = '', string $namespace = '', string $file_dir_name = ''): string|array|bool
    {
        if ($file_name === '') {
            $file_name = $this->file_name;
        }
        if ($namespace === '') {
            $namespace = $this->namespace;
        }
        if ($file_dir_name === '') {
            $file_dir_name = $this->file_dir_name;
        }
        $namespace    = str_replace('\\', '/', $namespace);
        $ns_path      = APPS_PATH . '/' . $namespace;
        $a_possible_locations = [
            'public_path'    => PUBLIC_PATH,
            'base_path'      => BASE_PATH,
            'src_path'       => SRC_PATH,
            'config_path'    => SRC_PATH . '/config',
            'private_path'   => PRIVATE_PATH,
            'ns_path'        => $ns_path,
            'ns_res_path'    => $ns_path . '/resources',
            'ns_tpl_path'    => $ns_path . '/resources/templates',
            'ns_conf_path'   => $ns_path . '/resources/config',
            'assets_path'    => PUBLIC_PATH . '/assets',
            'themes_path'    => PUBLIC_PATH . '/assets/themes/' . $this->theme_name,
            'default_theme'  => PUBLIC_PATH . '/assets/themes/default',
            'no_base'        => ''
        ];
        $file_w_dir = '/';
        $file_w_dir .= $file_dir_name !== '' ? $file_dir_name . '/' : '';
        $file_w_dir .= $file_name;
        foreach ($a_possible_locations as $location) {
            $full_path = $location . $file_w_dir;
            $full_path = str_replace('//', '/', $full_path);
            if (file_exists($full_path)) {
                return $full_path;
            }
        }
        return false;
    }

    /**
     * @param string $value
     * @return bool
     */
    public function setFileDirName($value = ''):bool
    {
        if ($value === '') { return false; }
        if (str_starts_with($value, '/')) {
            $value = substr($value, 1);
        }
        if (substr($value, strlen($value) - 1) === '/') {
            $value = substr($value, 0, -1);
        }
        $this->file_dir_name = $value;
        $this->setFileLocations();
        return true;
    }

    /**
     * @param $value
     * @return bool
     */
    public function setFileName($value):bool
    {
        $this->file_name = $value;
        $this->setFileLocations();
        return true;
    }

    /**
     * @param string $value
     * @return bool
     */
    public function setNamespace(string $value = ''):bool
    {
        $this->namespace = $value;
        $this->setFileLocations();
        return true;
    }

    /**
     * Sets the themename variable
     *
     * @param string $theme_name
     * @return bool
     */
    public function setThemeName(string $theme_name = 'default'): bool
    {
        $this->theme_name = $theme_name;
        $this->setFileLocations();
        return true;
    }

    /**
     * Sets the two main file paths. One is server path, one is site path (within web site)
     */
    protected function setFileLocations():void
    {
        $this->logIt(
            'File name: '  . $this->file_name .
            ' Namespace: ' . $this->namespace .
            ' file dir '   . $this->file_dir_name,
            LOG_OFF,
            __METHOD__ . '.' . __LINE__
        );
        $found_file = $this->locateFile($this->file_name, $this->namespace, $this->file_dir_name);
        $file_w_dir = str_replace(PUBLIC_PATH, '', $found_file);
        if (file_exists(PUBLIC_PATH . '/' . $file_w_dir)) {
            $this->file_w_dir  = $file_w_dir;
        } else {
            $this->file_w_dir = '';
        }
        $this->file_w_path = $found_file;
    }
}
