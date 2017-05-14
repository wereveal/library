<?php
/**
 * @brief     Determines the path to the file.
 * @ingroup   lib_helper
 * @file      Ritc/Library/Helper/Files.php
 * @namespace Ritc\Library\Helper
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   4.3.1+1
 * @date      2016-04-21 10:27:57
 * @note The constants with _DIR_NAME should correspond to dir names in
 *     the site theme or namespace (e.g. templates are in namespace). If a directory
 *     is missing, this could cause a fatal error.
 *     </pre>
 * @note <b>Change Log</b>
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
namespace Ritc\Library\Helper;

use Ritc\Library\Interfaces\LocationInterface;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class Files
 * @class Files
 * @package Ritc\Library\Helper
 */
class Files implements LocationInterface
{
    use LogitTraits;

    const CONFIG_DIR_NAME    = 'config';
    const CSS_DIR_NAME       = 'css';
    const HTML_DIR_NAME      = 'html';
    const IMAGE_DIR_NAME     = 'images';
    const JS_DIR_NAME        = 'js';
    const LIBS_DIR_NAME      = 'library';
    const PRIVATE_DIR_NAME   = 'private';
    const TEMPLATES_DIR_NAME = 'templates';
    const TMP_DIR_NAME       = 'tmp';
    /** @var string */
    protected $file_name     = 'no_file.tpl';
    /** @var string */
    protected $file_dir_name = '';
    /** @var string */
    protected $namespace;
    /** @var string */
    protected $file_w_dir;
    /** @var string */
    protected $file_w_path;
    /** @var string */
    protected $theme_name = '';

    /**
     * Files constructor.
     * @param string $file_name
     * @param string $the_directory
     * @param string $theme_name
     * @param string $namespace
     */
    public function __construct($file_name = '', $the_directory = '', $theme_name = '', $namespace = '')
    {
        if ($file_name != '') {
            $this->file_name = $file_name;
        }
        if ($the_directory != '') {
            $this->file_dir_name = $the_directory;
        }
        if ($theme_name != '') {
            $this->theme_name = $theme_name;
        }
        if ($namespace != '') {
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
    public function getVar($var_name)
    {
        return $this->$var_name;
    }

    /**
     * @param $file_name
     * @return bool
     */
    public function getConfigWithDir($file_name)
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
    public function getConfigWithPath($file_name)
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
     * @note Examples:
     *     getContents('test.tpl');\n
     *     getContents('test.css', 'css');\n
     *     getContents('test/test.tpl', 'templates');\n
     *     getContents('fred.html', 'my/file/path');
     * @param $file_name (str) - name of file - required - can include
     *     sub-paths to work with one of the several file_dir_name types,
     *     especially useful when using sub-dirs of templates and other
     *     sections of the theme.
     * @param $file_dir_name (str) - one of several types or raw path - optional
     * @return string - the contents of the file or false.
     */
    public function getContents($file_name = '', $file_dir_name = '')
    {
        if($file_name == '' && $this->file_name == "no_file.tpl") {
            $this->logIt("File name was blank.", LOG_OFF, __METHOD__);
            return false;
        }
        if ($file_dir_name != '') {
            $this->file_dir_name = $file_dir_name;
            if ($file_name == '') {
                /*
                    the file name has been set at a different time but
                    the file locations have been changed based on $file_dir_name
                    so the file locations have to be set again.
                */
                $this->setFileLocations();
            }
        }
        $file_path = $this->getFileWithPath($file_name);
        $file_contents = $file_path !== false ? file_get_contents($file_path) : false ;
        if($file_contents !== false) {
            return $file_contents;
        } else {
            $this->logIt("Could not get File Contents. File Path: " . $file_path, LOG_OFF, __METHOD__);
            return false;
        }
    }

    /**
     * @param $file_name
     * @return bool
     */
    public function getCssWithDir($file_name)
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
    public function getCssWithPath($file_name = '')
    {
        $this->file_dir_name = defined('CSS_DIR_NAME')
            ? CSS_DIR_NAME
            : self::CSS_DIR_NAME;
        return $this->getFileWithPath($file_name);
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->file_name;
    }

    /**
     * @return string
     */
    public function getFileDirName()
    {
        return $this->file_dir_name;
    }

    /**
     * @param string $file_name
     * @return bool|string
     */
    public function getFileWithDir($file_name = '')
    {
        if ($file_name != '') {
            $this->setFileName($file_name);
        }
        if (file_exists($this->file_w_path) && !is_dir($this->file_w_path)) {
            return $this->file_w_dir;
        } else {
            $log_from = __METHOD__ . '.' . __LINE__;
            $this->logIt("Couldn't get file on the following paths: $this->file_w_path", LOG_OFF, $log_from);
            return false;
        }
    }

    /**
     * @param string $file_name
     * @return bool|string
     */
    public function getFileWithPath($file_name = '')
    {
        if ($file_name != '') {
            $this->setFileName($file_name);
        }
        if (file_exists($this->file_w_path) && !is_dir($this->file_w_path)) {
            return $this->file_w_path;
        } else {
            $log_from = __METHOD__ . '.' . __LINE__;
            $this->logIt("The file doesn't exist on the following paths: {$this->file_w_path}", LOG_OFF, $log_from);
            return false;
        }
    }

    /**
     * @param $file_name
     * @return bool
     */
    public function getHtmlWithDir($file_name)
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
    public function getHtmlWithPath($file_name)
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
    public function getImageWithDir($file_name)
    {
        $this->file_dir_name = defined('IMAGE_DIR_NAME')
            ? IMAGE_DIR_NAME
            : self::IMAGE_DIR_NAME;
        return $this->getFileWithDir($file_name);
    }

    /**
     * @param $file_name
     * @return bool
     */
    public function getImageWithPath($file_name)
    {
        $this->file_dir_name = defined('IMAGE_DIR_NAME')
            ? IMAGE_DIR_NAME
            : self::IMAGE_DIR_NAME;
        return $this->getFileWithPath($file_name);
    }

    /**
     * @param $file_name
     * @return bool
     */
    public function getJsWithDir($file_name)
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
    public function getJsWithPath($file_name)
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
    public function getLibWithDir($file_name)
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
    public function getLibWithPath($file_name)
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
    public function getPrivateFile($file_name = '')
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
    public function getTmpFile($file_name = '')
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
    public function getTemplateWithDir($file_name)
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
    public function getTemplateWithPath($file_name)
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
    public function getFileLocations($which_one = '')
    {
        $a_file_locations = array();
        switch($which_one) {
            case "file_w_dir":
                $a_file_locations["file_w_dir"] = $this->file_w_dir;
                break;
            case "file_w_path":
                $a_file_locations["file_w_path"] = $this->file_w_path;
                break;
            default:
                $a_file_locations["file_w_dir"]    = $this->file_w_dir;
                $a_file_locations["file_w_path"]   = $this->file_w_path;
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
         * @param string $file_name required
     * @param string $namespace optional defaults to $this->namespace
     * @param string $file_dir_name optional default to none
     * @return mixed str $path_of_file or false
     */
    public function locateFile($file_name = '', $namespace = '', $file_dir_name = '')
    {
        if ($file_name == '') {
            $file_name = $this->file_name;
        }
        if ($namespace == '') {
            $namespace = $this->namespace;
        }
        if ($file_dir_name == '') {
            $file_dir_name = $this->file_dir_name;
        }
        $namespace    = str_replace('\\', '/', $namespace);
        $ns_path      = APPS_PATH . '/' . $namespace;
        $a_possible_locations = array(
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
            'no_base'        => '',
        );
        $file_w_dir = '/';
        $file_w_dir .= $file_dir_name != '' ? $file_dir_name . '/' : '';
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
    public function setFileDirName($value = '')
    {
        if ($value == '') { return false; }
        if (substr($value, 0, 1) == '/') {
            $value = substr($value, 1);
        }
        if (substr($value, strlen($value) - 1) == '/') {
            $value = substr($value, 0, strlen($value) - 1);
        }
        $this->file_dir_name = $value;
        $this->setFileLocations();
        return true;
    }

    /**
     * @param $value
     * @return bool
     */
    public function setFileName($value)
    {
        $this->file_name = $value;
        $this->setFileLocations();
        return true;
    }

    /**
     * @param string $value
     * @return bool
     */
    public function setNamespace($value = '')
    {
        $this->namespace = $value;
        $this->setFileLocations();
        return true;
    }

    /**
     * Sets the themename variable
     * @param string $theme_name
     * @return null
     */
    public function setThemeName($theme_name = 'default')
    {
        $this->theme_name = $theme_name;
        $this->setFileLocations();
        return true;
    }

    /**
     * Sets the two main file paths. One is server path, one is site path (within web site)
     */
    protected function setFileLocations()
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
