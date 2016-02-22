<?php
/**
 *  @brief     Creates the autoload_classmap.php file.
 *  @ingroup   ritc_library helper
 *  @file      ClassMapper.php
 *  @namespace Ritc\Library\Helper
 *  @class     Arrays
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

class ClassMapper
{
    /**
     * @var string
     */
    private $app_path;
    /**
     * @var string
     */
    private $config_path;
    /**
     * @var string
     */
    private $src_path;

    /**
     *  Constructor for the class.
     *  @param array $a_dirs should be [
     *      'app_path'    => '/some_path',
     *      'config_path' => '/some_path',
     *      'src_path'    => '/some_path'
     *  ]
     */
    public function __construct(array $a_dirs = array()) {
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
     */
    public function generateClassMap($src_path = '') {
        if ($src_path != '' && file_exists($src_path)) {
            $this->src_path =  $src_path;
        }
        $o_dir = new DirectoryIterator($this->src_path);
        $a_classmap = $this->createClassMapArray($o_dir, array());
        $classmap_text = "<?php\n/* Generated on " . date('c') . " by ClassMapper */\n\nreturn array(\n";
        $string_length = 0;
        foreach ($a_classmap as $key => $value) {
            $string_length = strlen($key) > $string_length ? strlen($key) : $string_length;
        }
        foreach ($a_classmap as $key => $value) {
            $padding = '';
            $key_length = strlen($key);
            if ($key_length < $string_length) {
                $pad_length = $string_length - $key_length;
                for ($i = 1 ; $i <= $pad_length ; $i++) {
                    $padding .= ' ';
                }
            }
            $value = str_replace($this->app_path . '/', '', $value);
            $classmap_text .= "    '{$key}'{$padding} => __DIR__ . '/../{$value}',\n";
            echo $key . "\n";
        }

        $classmap_text .= ');';
        file_put_contents($this->config_path . '/autoload_classmap.php', $classmap_text);
    }

    /**
     * @param \DirectoryIterator $o_dir
     * @param array              $a_classmap
     * @return array
     */
    private function createClassMapArray(DirectoryIterator $o_dir, array $a_classmap) {
        while ($o_dir->valid()) {
            $name = $o_dir->getFilename();
            if ($name != '.' && $name != '..') {
                if ($o_dir->isFile()) {
                    $path = $o_dir->getPath();
                    if (strpos($path, '/archive') === false) {
	                    $file_name = $path . "/" . $name;
	                    $o_file_info = new SplFileInfo($file_name);
	                    if ($o_file_info->getExtension() == 'php') {
	                        $file_real_path = $o_file_info->getRealPath();
	                        $file_contents = file_get_contents($file_real_path);
	                        $a_tokens = token_get_all($file_contents);
	                        // error_log($file_real_path);
	                        $namespace = $this->getNamespace($a_tokens);
	                        $classname = $this->getClassName($a_tokens);
	                        if (trim($namespace) != '' && trim($classname) != '') {
	                            // print $namespace . "\\" . $classname . " => " . $file_real_path . "\n";
	                            $left_side = trim($namespace) . "\\" . trim($classname);
	                            $a_classmap["{$left_side}"] = $file_real_path;
	                        }
	                    }
                    }
                }
                else {
                    $new_path = $o_dir->getPath() . "/" . $name;
                    $o_new_dir = new DirectoryIterator($new_path);
                    $a_classmap = $this->createClassMapArray($o_new_dir, $a_classmap);
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
    private function getClassName(array $a_tokens = array()) {
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
    private function getNamespace(array $a_tokens = array()) {
        $a_tokens2 = $a_tokens;
        $namespace = '';
        foreach ($a_tokens as $key => $a_token) {
            if (is_array($a_token)) {
                if ($a_token[0] == T_NAMESPACE) {
                    $line_number = $a_token[2];
                    foreach($a_tokens2 as $a_token2) {
                        if (isset($a_token2[2]) && $a_token2[2] == $line_number) {
                            // print $a_token2[0] . '  ' . token_name($a_token2[0]) . "\n";
                            switch ($a_token2[0]) {
                                case T_STRING:
                                    $namespace .= $a_token2[1];
                                    break;
                                case T_NS_SEPARATOR:
                                    $namespace .= $a_token2[1];
                                    break;
                                default:
                                    return '';
                            }
                        }
                    }
                }
            }
        }
        return $namespace;
    }
}
