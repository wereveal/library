<?php
/**
 *  Determines the path to the file.
 *  @file Files.php
 *  @ingroup wer_framework classes
 *  @class Files
 *  <pre>Determines the path to the file, primarily used for templates but
 *  can be used for anything.
 *  @see setFileLocations()
 *  @property CONFIG_DIR_NAME - the default directory name for configs
 *  @property CSS_DIR_NAME - the default directory name for css files
 *  @property HTML_DIR_NAME - the default directory name for html files
 *  @property IMAGE_DIR_NAME - the default directory name for the image files in a theme
 *  @property JS_DIR_NAME - the default directory name for js files
 *  @property LIBS_DIR_NAME - the default directory name for libraries
 *  @property PRIVATE_DIR_NAME - the default directory name for private files such as .htaccess
 *  @property TEMPLATES_DIR_NAME - the default directory name for templates
 *  @property TMP_DIR_NAME - the default directory name for temporary files
 *  @note <pre>The constants with _DIR_NAME should correspond to dir names in
 *      the site theme directory and the site manager theme directory
 *      with the exception of PRIVATE_DIR_NAME and TMP_DIR_NAME which
 *      really should be outside the web site but will default to
 *      the SM_INC_DIR if not otherwise specified.
 *      </pre>
 *  @author William Reveal <wer@wereveal.com>
 *  @version 4.0.0
 *  @date 2013-03-27 17:08:07
 *  @par Change Log
 *      v4.0.0 - FIG standards (mostly)
 *  @par Wer Framework 4.0.0
**/
namespace Wer\FrameworkBundle\Library;

class Files extends Location
{
    const CONFIG_DIR_NAME    = 'configs';
    const CSS_DIR_NAME       = 'css';
    const HTML_DIR_NAME      = 'html';
    const JS_DIR_NAME        = 'js';
    const LIBS_DIR_NAME      = 'libs';
    const IMAGE_DIR_NAME     = 'images';
    const PRIVATE_DIR_NAME   = 'private';
    const TEMPLATES_DIR_NAME = 'templates';
    const TMP_DIR_NAME       = 'tmp';
    protected $current_page;
    protected $file_name     = 'no_file.tpl';
    protected $file_dir_name = 'templates';
    protected $site_file_dir;
    protected $sm_file_dir;
    protected $sm_file_inc_dir;
    protected $site_file_path;
    protected $sm_file_path;
    protected $sm_file_inc_path;
    protected $sm_only       = false;
    protected $o_db;
    protected $o_elog;
    protected $private_properties;
    public function __construct($file_name = '', $the_directory = '')
    {
        $this->o_elog = Elog::start();
        $this->setPrivateProperties();
        if ($file_name != '') {
            $this->file_name = $file_name;
        }
        if ($the_directory != '') {
            $this->file_dir_name = $the_directory;
        }
        $this->setFileLocations();
    }
    public function getVar($var_name)
    {
        return $this->$var_name;
    }
    public function getConfigWithDir($file_name)
    {
        $this->file_dir_name = defined('CONFIGS_DIR_NAME')
            ? CONFIGS_DIR_NAME
            : self::CONFIGS_DIR_NAME;
        return $this->getFileWithDir($file_name);
    }
    public function getConfigWithPath($file_name)
    {
        $this->file_dir_name = defined('CONFIGS_DIR_NAME')
            ? CONFIGS_DIR_NAME
            : self::CONFIGS_DIR_NAME;
        return $this->getFileWithPath($file_name);
    }
    /**
     *  Returns the contents of a file as a string.
     *  Does more than the php function of file_get_contents in that
     *  file_get_contents requires a full path where as this only requires
     *  a file name. The path can be specified either directly or indirectly.
     *  @par Examples:
     *      getContents('test.tpl');\n
     *      getContents('test.css', 'css');\n
     *      getContents('test/test.tpl', 'templates');\n
     *      getContents('fred.html', 'my/file/path');
     *  @param $file_name (str) - name of file - required - can include
     *      sub-paths to work with one of the several file_dir_name types,
     *      especially useful when using sub-dirs of templates and other
     *      sections of the theme.
     *  @param $file_dir_name (str) - one of several types or raw path - optional
     *  @return string - the contents of the file or false.
    **/
    public function getContents($file_name = '', $file_dir_name = '')
    {
        $this->o_elog->setFromMethod(__METHOD__);
        if($file_name == '' && $this->file_name == "no_file.tpl") {
            $this->o_elog->write("File name was blank.", LOG_OFF);
            return false;
        }
        if ($file_dir_name != '') {
            $this->file_dir_name = $file_dir_name;
            if ($file_name == '') {
                /*
                    the file name has been set at a different time but
                    the file locations have been changed based on $file_dir_name
                    so the file locations have to be set again, kind of a kludge
                */
                $this->setFileLocations();
            }
        }
        $file_path = $this->getFileWithPath($file_name);
        $file_contents = $file_path !== false ? file_get_contents($file_path) : false ;
        if($file_contents !== false) {
            return $file_contents;
        } else {
            $this->o_elog->write("Could not get File Contents. File Path: " . $file_path, LOG_OFF);
            return false;
        }
    }
    public function getCssWithDir($file_name)
    {
        $this->file_dir_name = defined('CSS_DIR_NAME')
            ? CSS_DIR_NAME
            : self::CSS_DIR_NAME;
        return $this->getFileWithDir($file_name);
    }
    public function getCssWithPath($file_name = '')
    {
        $this->file_dir_name = defined('CSS_DIR_NAME')
            ? CSS_DIR_NAME
            : self::CSS_DIR_NAME;
        return $this->getFileWithPath($file_name);
    }
    public function getFileWithDir($file_name = '')
    {
        if ($file_name != '') {
            $this->setFileName($file_name);
        }
        if (file_exists($this->site_file_path) && !is_dir($this->site_file_path)) {
            return $this->site_file_dir;
        } elseif (file_exists($this->sm_file_inc_path) && !is_dir($this->sm_file_inc_path)) {
            return $this->sm_file_inc_dir;
        } elseif (file_exists($this->sm_file_path) && !is_dir($this->sm_file_path)) {
            return $this->sm_file_dir;
        } else {
            $this->o_elog->setFromMethod(__METHOD__ . '.' . __LINE__);
            $this->o_elog->write("Couldn't get file on the following paths: $this->site_file_path, $this->sm_file_inc_path, $this->sm_file_path", LOG_OFF);
            return false;
        }
    }
    public function getFileWithPath($file_name = '')
    {
        if ($file_name != '') {
            $this->setFileName($file_name);
        }
        if (file_exists($this->site_file_path) && !is_dir($this->site_file_path)) {
            return $this->site_file_path;
        } elseif (file_exists($this->sm_file_inc_path) && !is_dir($this->sm_file_inc_path)) {
            return $this->sm_file_inc_path;
        } elseif (file_exists($this->sm_file_path) && !is_dir($this->sm_file_path)) {
            return $this->sm_file_path;
        } else {
            $this->o_elog->setFromMethod(__METHOD__ . '.' . __LINE__);
            $this->o_elog->write("The file doesn't exist on the following paths: {$this->site_file_path}, {$this->sm_file_inc_path} or {$this->sm_file_path}", LOG_OFF);
            return false;
        }
    }
    public function getHtmlWithDir($file_name)
    {
        $this->file_dir_name = defined('HTML_DIR_NAME')
            ? HTML_DIR_NAME
            : self::HTML_DIR_NAME;
        return $this->getFileWithDir($file_name);
    }
    public function getHtmlWithPath($file_name)
    {
        $this->file_dir_name = defined('HTML_DIR_NAME')
            ? HTML_DIR_NAME
            : self::HTML_DIR_NAME;
        return $this->getFileWithPath($file_name);
    }
    public function getImageWithDir($file_name)
    {
        $this->file_dir_name = defined('IMAGE_DIR_NAME')
            ? IMAGE_DIR_NAME
            : self::IMAGE_DIR_NAME;
        return $this->getFileWithDir($file_name);
    }
    public function getImageWithPath($file_name)
    {
        $this->file_dir_name = defined('IMAGE_DIR_NAME')
            ? IMAGE_DIR_NAME
            : self::IMAGE_DIR_NAME;
        return $this->getFileWithPath($file_name);
    }
    public function getJsWithDir($file_name)
    {
        $this->file_dir_name = defined('JS_DIR_NAME')
            ? JS_DIR_NAME
            : self::JS_DIR_NAME;
        $this->setFileLocations();
        return $this->getFileWithDir($file_name);
    }
    public function getJsWithPath($file_name)
    {
        $this->file_dir_name = defined('JS_DIR_NAME')
            ? JS_DIR_NAME
            : self::JS_DIR_NAME;
        $this->setFileLocations();
        return $this->getFileWithPath($file_name);
    }
    public function getLibWithDir($file_name)
    {
        $this->file_dir_name = defined('LIBS_DIR_NAME')
            ? LIBS_DIR_NAME
            : self::LIBS_DIR_NAME;
        return $this->getFileWithDir($file_name);
    }
    public function getLibWithPath($file_name)
    {
        $this->file_dir_name = defined('LIBS_DIR_NAME')
            ? LIBS_DIR_NAME
            : self::LIBS_DIR_NAME;
        return $this->getFileWithPath($file_name);
    }
    public function getPrivateFile($file_name = '')
    {
        if (defined('PRIVATE_PATH')) {
            if (PRIVATE_PATH != '') {
                $private_path = PRIVATE_PATH;
            } else {
                $private_path = SITE_PATH . SM_INC_DIR . '/' . self::PRIVATE_DIR_NAME;
            }
        }
        $file_w_path = $private_path . '/' . $file_name;
        if (file_exists($file_w_path)) {
            return $file_w_path;
        }
        $this->o_elog->write("The File w/ Path didn't exist: '$file_w_path'. It is possible the Constant PRIVATE_PATH isn't set.", LOG_OFF, __METHOD__ . '.' . __LINE__);
        return false;
    }
    public function getTmpFile($file_name = '')
    {
        $file_w_path = TMP_PATH . '/' . $file_name;
        if (file_exists($file_w_path)) {
            return $file_w_path;
        }
        return false;
    }
    public function getTemplateWithDir($file_name)
    {
        $this->file_dir_name = defined('TEMPLATES_DIR_NAME')
            ? TEMPLATES_DIR_NAME
            : self::TEMPLATES_DIR_NAME;
        return $this->getFileWithDir($file_name);
    }
    public function getTemplateWithPath($file_name)
    {
        $this->file_dir_name = defined('TEMPLATES_DIR_NAME')
            ? TEMPLATES_DIR_NAME
            : self::TEMPLATES_DIR_NAME;
        $this->setFileLocations();
        return $this->getFileWithPath($file_name);
    }
    public function getFileLocations($which_one = '')
    {
        $a_file_locations = array();
        switch($which_one) {
            case "site_file_dir":
                $a_file_locations["site_file_dir"] = $this->site_file_dir;
                break;
            case "site_file_path":
                $a_file_locations["site_file_path"] = $this->site_file_path;
                break;
            case "sm_file_inc_dir":
                $a_file_locations["sm_file_inc_dir"] = $this->sm_file_inc_dir;
                break;
            case "sm_file_inc_path":
                $a_file_locations["sm_file_inc_path"] = $this->sm_file_inc_path;
                break;
            case "sm_file_dir":
                $a_file_locations["sm_file_dir"] = $this->sm_file_dir;
                break;
            case "sm_file_path":
                $a_file_locations["sm_file_path"] = $this->sm_file_path;
                break;
            default:
                if ($this->sm_only === false) {
                    $a_file_locations["site_file_dir"]    = $this->site_file_dir;
                    $a_file_locations["site_file_path"]   = $this->site_file_path;
                }
                $a_file_locations["sm_file_dir"]      = $this->sm_file_dir;
                $a_file_locations["sm_file_inc_dir"]  = $this->sm_file_inc_dir;
                $a_file_locations["sm_file_path"]     = $this->sm_file_path;
                $a_file_locations["sm_file_inc_path"] = $this->sm_file_inc_path;
        }
        return $a_file_locations;
    }
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
    public function setFileName($value)
    {
        $this->file_name = $value;
        $this->setFileLocations();
        return true;
    }
    public function setSmOnly($value = false)
    {
        $this->sm_only = $value;
    }
    /**
     *  Sets the possible places a file could exist.
     *    <pre>One of several places:
     *      /$this->file_dir_name
     *      /includes/$this->file_dir_name
     *      /themes/{chosen_theme}/$this->file_dir_name
     *      SM_DIR/themes/{chosen_theme}/$this->file_dir_name
     *      SM_INC_DIR/$this->file_dir_name</pre>
    **/
    protected function setFileLocations()
    {
        $this->o_elog->write($this->file_dir_name, LOG_OFF, __METHOD__);
        switch($this->file_dir_name) {
            case CSS_DIR_NAME:
                $file_dir = CSS_DIR;
                $sm_file_dir = SM_CSS_DIR;
                break;
            case JS_DIR_NAME:
                $file_dir = JS_DIR;
                $sm_file_dir = SM_JS_DIR;
                break;
            case IMAGE_DIR_NAME:
                $file_dir = THEME_IMAGE_DIR;
                $sm_file_dir = SM_THEME_IMAGE_DIR;
                break;
            case HTML_DIR_NAME:
                $file_dir = HTML_DIR;
                $sm_file_dir = SM_HTML_DIR;
                break;
            case TEMPLATES_DIR_NAME:
                if (defined('TEMPLATES_DIR')) {
                    $file_dir = TEMPLATES_DIR;
                    $sm_file_dir = SM_TEMPLATES_DIR;
                } else {
                    $file_dir = THEMES_DIR . '/default/templates';
                    $sm_file_dir = SM_THEMES_DIR . '/default/templates';
                }
                break;
            case CLASSES_DIR_NAME:
                $file_dir = CLASSES_DIR;
                $sm_file_dir = SM_CLASSES_DIR;
                break;
            case LIBS_DIR_NAME:
                $file_dir = LIBS_DIR;
                $sm_file_dir = SM_LIBS_DIR;
                break;
            case INCLUDES_DIR_NAME:
                $file_dir = INCLUDES_DIR;
                $sm_file_dir = SM_INC_DIR;
                break;
            default:
                $file_dir = "/" . $this->file_dir_name;
                $sm_file_dir = SM_DIR . "/" . $this->file_dir_name;
        }
        $this->o_elog->write("file_dir: " . $file_dir . " and sm_file_dir: " . $sm_file_dir, LOG_OFF, __METHOD__ . '.' . __LINE__);
        $this->o_elog->write("file_name: " . $this->file_name, LOG_OFF, __METHOD__ . '.' . __LINE__);
        if ($this->sm_only === false) {
            $this->site_file_dir  = $file_dir . "/" . $this->file_name;
            $this->site_file_path = SITE_PATH . $this->site_file_dir;
        } else {
            $this->site_file_dir  = '';
            $this->site_file_path = '';
        }
        $this->sm_file_dir      = $sm_file_dir . "/" . $this->file_name;
        $this->sm_file_path     = SITE_PATH . $this->sm_file_dir;
        $this->sm_file_inc_dir  = SM_INC_DIR . $file_dir . '/' . $this->file_name;
        $this->sm_file_inc_path = SITE_PATH . $this->sm_file_inc_dir;
    }
    /*
     *  Inherited from Location without change
     *  getFileName()
     *  getFileDirName()
     */
}
