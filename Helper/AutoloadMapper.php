<?php
/**
 * Class AutoloadMapper
 * @package Ritc_Library
 */
namespace Ritc\Library\Helper;

use DirectoryIterator;
use SplFileInfo;

/**
 * Creates the autoload_classmap.php file.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v2.0.0
 * @date    2021-11-29 15:10:51
 * @change_log
 * - v2.0.0 - updated for php8                                  - 2021-11-29 wer
 * - v1.3.0 - refactoring of file structure reflected here      - 2017-02-15 wer
 * - v1.2.1 - refactored var names to be more descriptive       - 12/07/2015 wer
 * - v1.2.0 - added code to not include archives                - 11/06/2015 wer
 * - v1.1.0 - added traits                                      - 09/01/2015 wer
 * - v1.0.0 - initial version
 */
class AutoloadMapper
{
    /** @var string */
    private mixed $src_path;
    /** @var string */
    private mixed $config_path;
    /** @var string */
    private mixed $apps_path;

    /**
     * Constructor for the class.
     * @param array $a_dirs should be <pre>[
     *     'src_path'    => '/some_path',
     *     'config_path' => '/some_path',
     *     'apps_path'    => '/some_path'
     * ]
     * </pre>
     */
    public function __construct(array $a_dirs = array())
    {
        $this->src_path    = empty($a_dirs['src_path'])    ? '/src'       : $a_dirs['src_path'];
        $this->config_path = empty($a_dirs['config_path']) ? '/src/config': $a_dirs['config_path'];
        $this->apps_path   = empty($a_dirs['apps_path'])   ? '/src/apps'  : $a_dirs['apps_path'];
    }

    /**
     * @param string $apps_path
     * @return bool
     */
    public function generateMapFiles(string $apps_path = ''):bool
    {
        $classmap_array_str = '';
        $ns_map_array_str   = '';
        if ($apps_path !== '' && file_exists($apps_path)) {
            $this->apps_path =  $apps_path;
        }
        $o_dir = new DirectoryIterator($this->apps_path);
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
            $value = str_replace($this->src_path . '/apps', '', $value);
            $classmap_array_str .= "    '{$key}'{$padding} => APPS_PATH . '{$value}',\n";
            // echo $key . "\n";

            /* Next namespace map buildup */
            $a_ns_parts = explode('\\', $key);
            $vendor_name = $a_ns_parts[0];
            if (!in_array($vendor_name, $a_existing_vendors, true)) {
                $a_existing_vendors[] = $vendor_name;
                $v_padding = '';
                $v_length = strlen($vendor_name);
                if ($v_length < $vendor_str_length) {
                    $pad_length = $vendor_str_length - $v_length;
                    for ($i = 1 ; $i <= $pad_length ; $i++) {
                        $v_padding .= ' ';
                    }
                }
                $ns_map_array_str   .= "    '{$vendor_name}\\\\'{$v_padding} => APPS_PATH . '/{$vendor_name}',\n";
                // echo $vendor_name . "\n";
            }
        }
        $ns_map_array_str = substr($ns_map_array_str, 0, -2);
        $classmap_array_str = substr($classmap_array_str, 0, -2);
        $date = date('c');
        $classmap_text =<<<EOT
<?php
/* Generated on {$date} by AutoloadMapper */
return [
{$classmap_array_str}
];
EOT;
        $ns_map_text =<<<EOT
<?php
/* Generated on {$date} by AutoloadMapper */
return [ 
{$ns_map_array_str}
];

EOT;
        file_put_contents($this->config_path . '/autoload_classmap.php', $classmap_text);
        file_put_contents($this->config_path . '/autoload_namespaces.php', $ns_map_text);
        return true;
    }

    /**
     * @param DirectoryIterator $o_dir
     * @param array             $a_classmap
     * @return array
     */
    private function createMapArray(DirectoryIterator $o_dir, array $a_classmap):array
    {
        while ($o_dir->valid()) {
            $name = $o_dir->getFilename();
            // echo $name . "\n";
            if ($name !== '.' && $name !== '..') {
                if ($o_dir->isFile()) {
                    $path = $o_dir->getPath();
                    if (!str_contains($path, '/archive')) {
	                    $file_name = $path . '/' . $name;
	                    // echo $file_name . "\n";
	                    $o_file_info = new SplFileInfo($file_name);
	                    if ($o_file_info->getExtension() === 'php') {
	                        $file_real_path = $o_file_info->getRealPath();
	                        $file_contents = file_get_contents($file_real_path);
	                        $a_tokens = token_get_all($file_contents);
	                        // print_r($a_tokens);
	                        // echo 'File real path: ' . $file_real_path . "\n";
	                        $namespace = $this->getNamespace($a_tokens);
	                        $classname = $this->getClassName($a_tokens);
	                        // echo 'Namespace: ' . $namespace . "\n";
	                        if (trim($namespace) !== '' && trim($classname) !== '') {
	                            // echo $namespace . "\\" . $classname . " => " . $file_real_path . "\n";
	                            $left_side = trim($namespace) . "\\" . trim($classname);
	                            $a_classmap[$left_side] = $file_real_path;
	                        }
	                    }
                    }
                }
                else {
                    $new_path = $o_dir->getPath() . '/' . $name;
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
    private function getClassName(array $a_tokens = array()): mixed
    {
        foreach ($a_tokens as $key => $a_token) {
            if (is_array($a_token)) {
                switch ($a_token[0]) {
                    case T_ABSTRACT:
                        return $a_tokens[$key+4][1];
                    case T_CLASS:
                    case T_INTERFACE:
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
    private function getNamespace(array $a_tokens = array()):string
    {
        $namespace = '';
        $line_number = -1;
        foreach ($a_tokens as $a_token) {
            if (is_array($a_token) && $a_token[0] === T_NAMESPACE) {
                $line_number = $a_token[2];
                break;
            }
        }
        foreach($a_tokens as $a_token) {
            if (isset($a_token[2]) && $a_token[2] === $line_number) {
                // echo $a_token[0] . '  ' . token_name($a_token[0]) . "\n";
                $namespace .= match ($a_token[0]) {
                    T_NS_SEPARATOR, T_STRING => $a_token[1],
                    default                  => '',
                };
            }
        }
        return $namespace;
    }

    /**
     * Returns the app path.
     * @return string
     */
    public function getSrcPath():string
    {
        return $this->src_path;
    }

    /**
     * Sets the app path.
     *
     * @param string $value
     */
    public function setSrcPath(string $value = ''):void
    {
        $this->src_path = $value !== ''
            ? $value
            : $this->src_path;
    }

    /**
     * Return the config path.
     * @return string
     */
    public function getConfigPath():string
    {
        return $this->config_path;
    }

    /**
     * Sets the config path.
     *
     * @param string $value
     */
    public function setConfigPath(string $value = ''):void
    {
        $this->config_path = $value !== ''
            ? $value
            : $this->config_path;
    }

    /**
     * Returns the apps path.
     * @return string
     */
    public function getAppsPath():string
    {
        return $this->apps_path;
    }

    /**
     * Sets the apps path.
     *
     * @param string $value
     */
    public function setAppsPath(string $value = ''):void
    {
        $this->apps_path = $value !== ''
            ? $value
            : $this->apps_path;
    }
}
