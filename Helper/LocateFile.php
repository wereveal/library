<?php
/**
 * @brief     Finds a file and returns it with the full server path or web site path.
 * @detail    Based on the other similar helper, Ritc\Library\Helper\Files. However, this one uses all static functions.
 * @ingroup   lib_helper
 * @file      Ritc/Library/Helper/LocateFile.php
 * @namespace Ritc\Library\Helper
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.1
 * @date      2017-06-10 07:22:10
 * @note <b>Change Log</b>
 * - v1.0.1  Bug fix                                         - 2017-06-10 wer
 * - v1.0.0  Initial Version                                 - 2017-02-08 wer
 */
namespace Ritc\Library\Helper;

/**
 * Class LocateFile
 * @class LocateFile
 * @package Ritc\Library\Helper
 */
class LocateFile
{
    /**
     * @param string $file_name
     * @param string $namespace
     * @return string
     */
    public static function getConfigWithDir($file_name = '', $namespace = '')
    {
        $file_w_path = self::getConfigWithPath($file_name, $namespace);
        if ($file_w_path != '') {
            return str_replace(BASE_PATH, '', $file_w_path);
        }
        return '';
    }

    /**
     * @param string $file_name
     * @param string $namespace
     * @return string
     */
    public static function getConfigWithPath($file_name = '', $namespace = '')
    {
        if ($namespace == '') {
            $path = SRC_CONFIG_PATH;
        }
        else {
            $ns_path = str_replace('\\', '/', $namespace);
            $ns_path .= '/resources/config';
            $path = APPS_PATH . '/' . $ns_path;
        }
        $file_w_path = $path . '/' . $file_name;
        if (file_exists($file_w_path)) {
            return $file_w_path;
        }
        return '';
    }

    /**
     * @param $file_name
     * @return bool
     */
    public static function getCssWithDir($file_name, $namespace = '')
    {
        $file_w_path = self::getCssWithPath($file_name, $namespace);
        return str_replace(PUBLIC_PATH, '', $file_w_path);
    }

    /**
     * @param string $file_name
     * @param string $namespace
     * @return string
     */
    public static function getCssWithPath($file_name = '', $namespace = '')
    {
        if ($namespace == '') {
            $a_optional_paths = [
                PUBLIC_PATH . '/' . CSS_DIR_NAME . '/' . $file_name,
                PUBLIC_PATH . '/assets/' . CSS_DIR_NAME . '/' . $file_name,
                PUBLIC_PATH . '/' . $file_name
            ];
        }
        else {
            $ns_path = str_replace('\\', '/', $namespace);
            $ns_path .= '/resources/themes';
            $a_optional_paths = [
                APPS_PATH . '/' . $ns_path . '/' . THEME_NAME . '/' . CSS_DIR_NAME,
                APPS_PATH . '/' . $ns_path . '/default/' . CSS_DIR_NAME,
                APPS_PATH . '/' . $ns_path . '/' . CSS_DIR_NAME
            ];
        }
        foreach ($a_optional_paths as $path) {
            $file_w_path = $path . '/' . $file_name;
            if (file_exists($file_w_path)) {
                return $file_w_path;
            }
        }
        return '';
    }

    /**
     * @param string $file_name
     * @return bool
     */
    public static function getFileWithDir($file_name = '', $namespace = '')
    {
        $file_w_path = self::getFileWithPath($file_name, $namespace);
        if ($file_w_path != '') {
            return str_replace(PUBLIC_PATH, '', $file_w_path);
        }
        return '';
    }

    /**
     * @param string $file_name
     * @return bool
     */
    public static function getFileWithPath($file_name = '', $namespace = '')
    {
        if ($namespace == '') {
            $path = PUBLIC_PATH;
        }
        else {
            $ns_path = str_replace('\\', '/', $namespace);
            $ns_path .= '/resources/files';
            $path = APPS_PATH . '/' . $ns_path;
        }
        $file_w_path = $path . '/' . $file_name;
        if (file_exists($file_w_path)) {
            return $file_w_path;
        }
        return '';
    }

    /**
     * Get the full path and file name for an image.
     * @param string $file_name
     * @param string $namespace
     * @return string
     */
    public static function getImageWithPath($file_name = '', $namespace = '')
    {
        if ($namespace == '') {
            $a_optional_paths = [
                PUBLIC_PATH . '/' . IMAGE_DIR_NAME  . '/' . $file_name,
                PUBLIC_PATH . '/assets/' . IMAGE_DIR_NAME  . '/' . $file_name,
                PUBLIC_PATH . '/' . $file_name
            ];
        }
        else {
            $ns_path = str_replace('\\', '/', $namespace);
            $ns_path .= '/resources/themes';
            $a_optional_paths = [
                APPS_PATH . '/' . $ns_path . '/' . THEME_NAME . '/' . IMAGE_DIR_NAME,
                APPS_PATH . '/' . $ns_path . '/default/' . IMAGE_DIR_NAME,
                APPS_PATH . '/' . $ns_path . '/' . IMAGE_DIR_NAME
            ];
        }
        foreach ($a_optional_paths as $path) {
            $file_w_path = $path . '/' . $file_name;
            if (file_exists($file_w_path)) {
                return $file_w_path;
            }
        }
        return '';
    }

    /**
     * @param $file_name
     * @return bool
     */
    public static function getImageWithDir($file_name = '', $namespace = '')
    {
        $file_w_path = self::getImageWithPath($file_name, $namespace);
        return str_replace(PUBLIC_PATH, '', $file_w_path);
    }

    /**
     * @param $file_name
     * @return bool
     */
    public static function getJsWithPath($file_name = '', $namespace = '')
    {
        if ($namespace == '') {
            $a_optional_paths = [
                PUBLIC_PATH . '/' . JS_DIR_NAME . '/' . $file_name,
                PUBLIC_PATH . '/assets/' . JS_DIR_NAME . '/' . $file_name,
                PUBLIC_PATH . '/' . $file_name
            ];
        }
        else {
            $ns_path = str_replace('\\', '/', $namespace);
            $ns_path .= '/resources/themes';
            $a_optional_paths = [
                APPS_PATH . '/' . $ns_path . '/' . THEME_NAME . '/' . JS_DIR_NAME,
                APPS_PATH . '/' . $ns_path . '/default/' . JS_DIR_NAME,
                APPS_PATH . '/' . $ns_path . '/' . JS_DIR_NAME
            ];
        }
        foreach ($a_optional_paths as $path) {
            $file_w_path = $path . '/' . $file_name;
            if (file_exists($file_w_path)) {
                return $file_w_path;
            }
        }
        return '';
    }

    /**
     * @param string $file_name
     * @param string $namespace
     * @return mixed
     */
    public static function getJsWithDir($file_name = '', $namespace = '')
    {
        $file_w_path = self::getJsWithPath($file_name, $namespace);
        return str_replace(PUBLIC_PATH, '', $file_w_path);
    }

    /**
     * @param string $file_name
     * @return bool|string
     */
    public static function getPrivateFile($file_name = '')
    {
        $private_path = $_SERVER['DOCUMENT_ROOT'] . '/../private';
        $file_w_path = $private_path . '/' . $file_name;
        if (file_exists($file_w_path)) {
            return $file_w_path;
        }
        return false;
    }

    /**
     * @param string $file_name
     * @return bool|string
     */
    public static function getTmpFile($file_name = '')
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
    public static function getTemplateWithDir($file_name = '', $namespace = '', $sub_dir = '')
    {
        $path = self::getTemplateWithPath($file_name, $namespace, $sub_dir);
        if ($path != '') {
            return str_replace(PUBLIC_PATH, '', $path);
        }
        return '';
    }

    /**
     * @param $file_name
     * @return bool
     */
    public static function getTemplateWithPath($file_name = '', $namespace = '', $sub_dir = '')
    {
        if ($file_name == '' || $namespace == '') { return ''; }
        $path = APPS_PATH . '/';
        $path .= str_replace('\\', '/', $namespace);
        $path .= $sub_dir != '' ? '/resources/templates/' . $sub_dir . '/' : '';
        $path .= $path . $file_name;
        if (file_exists($path)) {
            return $path;
        }
        return '';
    }

    /**
     * Returns the full path and file name for file.
     * @param string $file_name
     * @param string $namespace
     * @param string $sub_dir
     * @return string
     */
    public static function getTestFileWithPath($file_name = '', $namespace = '', $sub_dir = '')
    {
        if ($file_name == '' || $namespace == '') { return ''; }
        $path = APPS_PATH . '/';
        $path .= str_replace('\\', '/', $namespace);
        $path .= $sub_dir != ''
            ? '/resources/config/tests/' . $sub_dir . '/'
            : '/resources/config/tests/';
        $path .= $file_name;
        if (file_exists($path)) {
            return $path;
        }
        return '';
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
     *     PUBLIC_DIR/$file_name
     *     SRC_PATH/config/$file_name
     *     SRC_PATH/$file_dir_name/$file_name
     *     SRC_PATH/str_replace('Ritc\', '', $namespace)/$file_name
         * @param string $file_name required
     * @param string $namespace optional defaults to $this->namespace
     * @param string $file_dir_name optional default to none
     * @return mixed str $path_of_file or false
     */
    public static function locateFile($file_name = '', $namespace = '', $file_dir_name = '', $theme_name = 'default')
    {
        if ($file_name == '') {
            return '';
        }
        $namespace    = str_replace('\\', '/', $namespace);
        $ns_path      = APPS_PATH . '/' . $namespace;
        $a_possible_locations = array(
            'public_path'           => PUBLIC_PATH,
            'base_path'             => BASE_PATH,
            'src_path'              => SRC_PATH,
            'config_path'           => SRC_CONFIG_PATH,
            'private_path'          => PRIVATE_PATH,
            'ns_path'               => $ns_path,
            'ns_res_path'           => $ns_path . '/resources',
            'ns_tpl_path'           => $ns_path . '/resources/templates',
            'ns_conf_path'          => $ns_path . '/resources/config',
            'ns_theme_path'         => $ns_path . '/resources/themes/' . $theme_name,
            'ns_default_theme_path' => $ns_path . '/resources/themes/default',
            'assets_path'           => PUBLIC_PATH . '/assets',
            'themes_path'           => PUBLIC_PATH . '/assets/themes/' . $theme_name,
            'default_theme'         => PUBLIC_PATH . '/assets/themes/default',
            'no_base'               => '',
        );
        $file_w_dir = '/';
        $file_w_dir .= $file_dir_name != '' ? $file_dir_name . '/' : '';
        $file_w_dir .= $file_name;
        foreach ($a_possible_locations as $location) {
            $full_path = $location . $file_w_dir;
            $full_path = str_replace('//', '/', $full_path);
            if (file_exists($full_path)) {
                $a_file_paths = [
                    'path' => $full_path,
                    'dir'  => str_replace(PUBLIC_PATH, '', $full_path)
                ];
                return $a_file_paths;
            }
        }
        return false;
    }
}
