<?php
/**
 *  @brief     Creates the autoload_classmap.php file.
 *  @ingroup   ritc_library lib_helper
 *  @file      Ritc/Library/Helper/AutoloadMapper.php
 *  @namespace Ritc\Library\Helper
 *  @class     AutoloadMapper
 *  @author    William E Reveal <bill@revealitconsulting.com>
 *  @version   1.2.2
 *  @date      2016-02-22 15:00:10
 *  @note <pre><b>Change Log</b>
 *      v1.2.2 - bug fix                                     - 02/22/2016 wer
 *      v1.2.1 - refactored var names to be more descriptive - 12/07/2015 wer
 *      v1.2.0 - added code to not include archives          - 11/06/2015 wer
 *      v1.1.0 - added traits                                - 09/01/2015 wer
 *      v1.0.0 - initial version
 *  </pre>
**/
namespace Ritc\Library\Helper;

use \DirectoryIterator;
use \SplFileInfo;

class AutoloadMapper
{
    /** @var string  */
    private $app_path;
    /** @var string  */
    private $config_path;
    /** @var string  */
    private $src_path;

    /**
     *  Constructor for the class.
     *  @param array $a_dirs should be [
     *      'app_path'    => '/some_path',
     *      'config_path' => '/some_path',
     *      'src_path'    => '/some_path'
     *  ]
     */
    public function __construct(array $a_dirs = array())
    {
        $this->app_path = isset($a_dirs['app_path'])
            ? $a_dirs['app_path']
            : '/app';
        $this->config_path = isset($a_dirs['config_path'])
            ? $a_dirs['config_path']
            : '/app/config';
        $this->src_path = isset($a_dirs['src_path'])
            ? $a_dirs['src_path']
            : '/app/src';
    }

    /**
     * @param string $src_path
     * @return null
     */
    public function generateMapFiles($src_path = '')
    {
        $classmap_array_str = '';
        $ns_map_array_str   = '';
        if ($src_path != '' && file_exists($src_path)) {
            $this->src_path =  $src_path;
        }
        $o_dir = new DirectoryIterator($this->src_path);
        if (!is_object($o_dir)) {
            return false;
        }
        $a_classmap = $this->createMapArray($o_dir, array());
        // print_r($a_classmap);
        /* get the longest length of the namespace and vendor name */
        $ns_str_length = 0;
        $vendor_str_length = 0;
        foreach ($a_classmap as $key => $value) {
            $ns_str_length = strlen($key) > $ns_str_length
                ? strlen($key)
                : $ns_str_length;
            $a_ns_parts = explode('\\', $key);
            $vendor_str_length = strlen($a_ns_parts[0]) > $vendor_str_length
                ? strlen($a_ns_parts[0])
                : $vendor_str_length;
        }
        /* Go over the array again, now building the string */
        $a_existing_vendors = [];
        foreach ($a_classmap as $key => $value) {
            /* First classmap string buildup */
            $padding = '';
            $key_length = strlen($key);
            if ($key_length < $ns_str_length) {
                $pad_length = $ns_str_length - $key_length;
                for ($i = 1 ; $i <= $pad_length ; $i++) {
                    $padding .= ' ';
                }
            }
            $value = str_replace($this->app_path . '/', '', $value);
            $classmap_array_str .= "    '{$key}'{$padding} => __DIR__ . '/../{$value}',\n";
            // echo $key . "\n";

            /* Next namespace map buildup */
            $a_ns_parts = explode('\\', $key);
            $vendor_name = $a_ns_parts[0];
            if (array_search($vendor_name, $a_existing_vendors) === false) {
                $a_existing_vendors[] = $vendor_name;
                $v_padding = '';
                $v_length = strlen($vendor_name);
                if ($v_length < $vendor_str_length) {
                    $pad_length = $vendor_str_length - $v_length;
                    for ($i = 1 ; $i <= $pad_length ; $i++) {
                        $v_padding .= ' ';
                    }
                }
                $value =
                $ns_map_array_str   .= "    '{$vendor_name}\\\\'{$v_padding} => __DIR__ . '/../src/{$vendor_name}',\n";
                // echo $vendor_name . "\n";
            }
        }

        $date = date('c');
        $classmap_text =<<<EOT
<?php
* Generated on {$date} by AutoloadMapper *
return array(
{$classmap_array_str}
);
EOT;
        $ns_map_text =<<<EOT
<?php
* Generated on {$date} by AutoloadMapper *
return array(
{$ns_map_array_str}
);

EOT;
        file_put_contents($this->config_path . '/autoload_classmap.php', $classmap_text);
        file_put_contents($this->config_path . '/autoload_namespaces.php', $ns_map_text);
    }

    /**
     * @param \DirectoryIterator $o_dir
     * @param array              $a_classmap
     * @return array
     */
    private function createMapArray(DirectoryIterator $o_dir, array $a_classmap)
    {
        while ($o_dir->valid()) {
            $name = $o_dir->getFilename();
            // echo $name . "\n";
            if ($name != '.' && $name != '..') {
                if ($o_dir->isFile()) {
                    $path = $o_dir->getPath();
                    if (strpos($path, '/archive') === false) {
	                    $file_name = $path . "/" . $name;
	                    // echo $file_name . "\n";
	                    $o_file_info = new SplFileInfo($file_name);
	                    if ($o_file_info->getExtension() == 'php') {
	                        $file_real_path = $o_file_info->getRealPath();
	                        $file_contents = file_get_contents($file_real_path);
	                        $a_tokens = token_get_all($file_contents);
	                        // print_r($a_tokens);
	                        // echo 'File real path: ' . $file_real_path . "\n";
	                        $namespace = $this->getNamespace($a_tokens);
	                        $classname = $this->getClassName($a_tokens);
	                        // echo 'Namespace: ' . $namespace . "\n";
	                        if (trim($namespace) != '' && trim($classname) != '') {
	                            // echo $namespace . "\\" . $classname . " => " . $file_real_path . "\n";
	                            $left_side = trim($namespace) . "\\" . trim($classname);
	                            $a_classmap["{$left_side}"] = $file_real_path;
	                        }
	                    }
                    }
                }
                else {
                    $new_path = $o_dir->getPath() . "/" . $name;
                    $o_new_dir = new DirectoryIterator($new_path);
                    $a_classmap = $this->createMapArray($o_new_dir, $a_classmap);
                }
            }
            $o_dir->next();
        }
        return $a_classmap;
    }

    /**
     * @param array $a_tokens
     * @return mixed
     */
    private function getClassName(array $a_tokens = array())
    {
        foreach ($a_tokens as $key => $a_token) {
            if (is_array($a_token)) {
                switch ($a_token[0]) {
                    case T_ABSTRACT:
                        return $a_tokens[$key+4][1];
                    case T_CLASS:
                        return $a_tokens[$key+2][1];
                    case T_INTERFACE:
                        return $a_tokens[$key+2][1];
                    case T_TRAIT:
                        return $a_tokens[$key+2][1];
                    default:
                        // do nothing
                }
            }
        }
        return '';
    }

    /**
     * @param array $a_tokens
     * @return string
     */
    private function getNamespace(array $a_tokens = array())
    {
        $a_tokens2 = $a_tokens;
        $namespace = '';
        foreach ($a_tokens as $key => $a_token) {
            if (is_array($a_token)) {
                if ($a_token[0] == T_NAMESPACE) {
                    $line_number = $a_token[2];
                    break;
                }
            }
        }
        foreach($a_tokens as $a_token) {
            if (isset($a_token[2]) && $a_token[2] == $line_number) {
                // echo $a_token[0] . '  ' . token_name($a_token[0]) . "\n";
                switch ($a_token[0]) {
                    case T_STRING:
                        $namespace .= $a_token[1];
                        break;
                    case T_NS_SEPARATOR:
                        $namespace .= $a_token[1];
                        break;
                    default:
                        $namespace .= '';
                }
            }
        }
        return $namespace;
    }

    public function getAppPath()
    {
        return $this->app_path;
    }
    public function setAppPath($value = '')
    {
        $this->app_path = $value != ''
            ? $value
            : $this->app_path;
    }
    public function getConfigPath()
    {
        return $this->config_path;
    }
    public function setConfigPath($value = '')
    {
        $this->config_path = $value != ''
            ? $value
            : $this->config_path;
    }
    public function getSrcPath()
    {
        return $this->src_path;
    }
    public function setSrcPath($value = '')
    {
        $this->src_path = $value != ''
            ? $value
            : $this->src_path;
    }
}
