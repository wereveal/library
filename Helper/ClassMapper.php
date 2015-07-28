<?php
/**
 *  @brief Creates the autoload_classmap.php file.
 *  @file ClassMapper.php
 *  @ingroup ritc_library helper
 *  @namespace Ritc/Library/Helper
 *  @class Arrays
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0β1
 *  @date 2015-07-25 13:44:05
 *  @note A part of the RITC Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.0β1
 *  </pre>
**/
namespace Ritc\Library\Helper;

use \DirectoryIterator;
use \SplFileInfo;

class ClassMapper
{
    private $app_dir;
    private $config_dir;
    private $src_dir;

    /**
     *  Constructor for the class.
     *  @param array $a_dirs should be [
     *      'app_dir'    => '/some_path',
     *      'config_dir' => '/some_path',
     *      'src_dir'    => '/some_path'
     *  ]
     */
    public function __construct(array $a_dirs = array()) {
        $this->app_dir = isset($a_dirs['app_dir'])
            ? $a_dirs['app_dir']
            : '/app';
        $this->config_dir = isset($a_dirs['config_dir'])
            ? $a_dirs['config_dir']
            : '/app/config';
        $this->src_dir = isset($a_dirs['src_dir'])
            ? $a_dirs['src_dir']
            : '/app/src';
    }
    public function generateClassMap($src_dir = '') {
        if ($src_dir != '' && file_exists($src_dir)) {
            $this->src_dir =  $src_dir;
        }
        $o_dir = new DirectoryIterator($this->src_dir);
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
            $value = str_replace($this->app_dir . '/', '', $value);
            $classmap_text .= "    '{$key}'{$padding} => __DIR__ . '/../{$value}',\n";
        }

        $classmap_text .= ');';
        file_put_contents($this->config_dir . '/autoload_classmap.php', $classmap_text);
    }

    private function createClassMapArray(DirectoryIterator $o_dir, array $a_classmap) {
        while ($o_dir->valid()) {
            $name = $o_dir->getFilename();
            if ($name != '.' && $name != '..') {
                if ($o_dir->isFile()) {
                    $file_name = $o_dir->getPath() . "/" . $name;
                    $o_file_info = new SplFileInfo($file_name);
                    if ($o_file_info->getExtension() == 'php') {
                        $file_real_path = $o_file_info->getRealPath();
                        $file_contents = file_get_contents($file_real_path);
                        $a_tokens = token_get_all($file_contents);
                        $namespace = $this->getNamespace($a_tokens);
                        $classname = $this->getClassName($a_tokens);
                        if (trim($namespace) != '' && trim($classname) != '') {
                            // print $namespace . "\\" . $classname . " => " . $file_real_path . "\n";
                            $left_side = trim($namespace) . "\\" . trim($classname);
                            $a_classmap["{$left_side}"] = $file_real_path;
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

    private function getClassName(array $a_tokens = array()) {
        $a_tokens2 = $a_tokens;
        foreach ($a_tokens as $key => $a_token) {
            if (is_array($a_token)) {
                switch ($a_token[0]) {
                    case T_ABSTRACT:
                        break;
                    case T_CLASS:
                        return $a_tokens[$key+2][1];
                    case T_INTERFACE:
                        return $a_tokens[$key+2][1];
                    default:
                        // do nothing
                }
            }
        }
    }
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
                                    // do nothing
                            }
                        }
                    }
                }
            }
        }
        return $namespace;
    }
}
