<?php
/**
 * Class Html
 * @package RITC_Library
 */
namespace Ritc\Library\Helper;

use Ritc\Library\Traits\LogitTraits;

/**
 * Creates HTML strings.
 * Methods which start with make_ return a modified version
 * of the value passed into the method, usually indicated by
 * the name of the method
 *
 * @author     William E Reveal <bill@revealitconsulting.com>
 * @version    v1.0.6
 * @date       2015-09-01 07:38:42
 * @deprecated v1.0.6
 * ## Change Log
 * - v1.0.6 - removed abstract class Base, used LogitTraits            - 09/01/2015 wer
 * - v1.0.5 - Refactored to match the Arrays class                     - 07/31/2015 wer
 * - v1.0.4 - moved to the Ritc\Library\Helper namespace               - 11/15/2014 wer
 * - v1.0.3 - changed to implment the changes in Base class            - 09/23/2014 wer
 * - v1.0.2 - some refactoring changes based on changes in package     - 12/19/2013 wer
 * - v1.0.1 - some refactoring changes based on changes in other files - 03/17/2013 wer
 */
class Html
{
    use LogitTraits;

    /** @var Arrays  */
    protected $o_arrays;
    /** @var Files  */
    protected $o_files;
    /** @var Strings  */
    private   $o_str;
    /** @var string  */
    protected $the_original_string = 'Start';
    /** @var string */
    protected $the_modified_string = '';
    /** @var string  */
    protected $template_name     = 'default.twig';
    /** @var string  */
    protected $namespace = 'Ritc';

    /**
     * Html constructor.
     */
    public function __construct()
    {
        $this->o_str    = new Strings;
        $this->o_arrays = new Arrays();
        $this->o_files  = new Files('main.twig', 'templates',  'default', 'Ritc');
    }

    /**
     * @param array $a_button_values
     * @return string
     */
    public function button($a_button_values = array())
    {
        $a_button_values = Arrays::removeUndesiredPairs(
            $a_button_values,
            array(
                'button_color',
                'button_size',
                'button_id',
                'button_name',
                'button_value',
                'button_text',
                'tab_index'
            )
        );
        $a_default_values = array(
            'button_color' => 'white',
            'button_size'  => 'medium',
            'button_id'    => '',
            'button_name'  => 'action',
            'button_value' => 'not_a_good_value',
            'button_text'  => 'Needs Some Text',
            'tab_index'    => '10'
        );
        if ($a_button_values == array()) {
            $a_button_values = $a_default_values;
        }
        if ($a_button_values['button_id'] == '' && $a_button_values['button_value'] != '') {
            $a_button_values['button_id'] = $a_button_values['button_value'];
        }
        $a_button_values = array_merge($a_default_values, $a_button_values);
        return $this->render('elements/button.tpl', $a_button_values, true);
    }

    /**
     * @param string $css_file
     * @param string $css_dir
     * @param string $css_media
     * @return string
     */
    public function cssLink($css_file = '', $css_dir = 'css', $css_media = 'screen')
    {
        if($css_file == '') {
            $this->logIt('A Problem Has Occurred. The css file must be specified', LOG_OFF, __METHOD__ . '.' . __LINE__);
            return '';
        }
        $this->o_files->setFileDirName($css_dir);
        $a_tpl_values = array(
            'css_media'  => ($css_media),
            'css_source' => $this->o_files->getFileWithDir($css_file));
        return $this->render('css.tpl', $a_tpl_values, true);
    }

    /**
     * @param string $message
     * @param string $image_file
     * @return string
     */
    public function failure($message = 'A Problem Has Occurred. Please Try Again.', $image_file = '')
    {
        if ($image_file == '') {
            $image = $this->o_files->getImageWithDir('icons/failure.png');
        } else {
            $image = $this->o_files->getImageWithDir($image_file);
            if ($image === false) {
                $image = $this->o_files->getImageWithDir('icons/failure.png');
            }
        }
        $this->logIt("Image w Dir: {$image}", LOG_OFF, __METHOD__ . '.' . __LINE__);
        $a_stuff = array('message' => $message, 'image'=>$image, 'class'=>'msg-failure', 'alt'=>'A Problem Has Occurred');
        return $this->render('message.tpl', $a_stuff, true);
    }

    /**
     * @param string $js_file
     * @param string $js_dir
     * @return string
     */
    public function jsLink($js_file = '', $js_dir = 'js')
    {
        if($js_file == '') {
            $this->logIt('A Problem Has Occurred. The JavaScript File must be specified', LOG_OFF, __METHOD__ . '.' . __LINE__);
            return '';
        }
        $this->o_files->setFileDirName($js_dir);
        $a_tpl_values = array('js_source'=>$this->o_files->getFileWithDir($js_file));
        return $this->render('js.tpl', $a_tpl_values, true);
    }

    /**
     * Returns a formatted string for a success message.
     * @param string $message
     * @param string $image_file
     * @return string
     */
    public function success($message = 'Success!', $image_file = '')
    {
        if ($image_file == '') {
            $image = $this->o_files->getImageWithDir('icons/success.png');
        } else {
            $image = $this->o_files->getImageWithDir($image_file);
            if ($image === false) {
                $image = $this->o_files->getImageWithDir('icons/success.png');
            }
        }
        $a_stuff = array('message' => $message, 'image'=>$image, 'class'=>'msg-success', 'alt'=>'Success');
        return $this->render('message.tpl', $a_stuff, true);
    }

    /**
     * Returns formated html for a warning message.
     * @param string $message
     * @param string $image_file
     * @return string
     */
    public function warning($message = "Warning!", $image_file = '')
    {
        if ($image_file == '') {
            $image = $this->o_files->getImageWithDir('icons/warning.png');
        } else {
            $image = $this->o_files->getImageWithDir($image_file);
            if ($image === false) {
                $image = $this->o_files->getImageWithDir('icons/warning.png');
            }
        }
        $a_stuff = array('message' => $message, 'image'=>$image, 'class'=>'msg-warning', 'alt'=>'Warning');
        return $this->render('message.tpl', $a_stuff, true);
    }

    /**
     * Fill the template with the values passed in.
     * @param mixed $template may be the name of a file or a string. If
     *     a name of a file, the file must be in the templates directory
     *     of the in use theme. Suggestion is simple, if the same template
     *     is being used multiple time consecutively, put the template in
     *     a string once and pass it into the fill template method.
     * @param array $a_values the values to insert into the template in
     *     an assoc array. $key is the string to find $value is the replacement.
     * @param bool $is_file the template is the path to a file, defaults to false
     * @return string - the filled in template
     */
    public function render($template = '', array $a_values = array(), $is_file = false)
    {
        if ($is_file) {
            $file_with_path = $this->o_files->getFileWithPath($template);
            if (file_exists($file_with_path)) {
                $this->logIt("Template: " . $template . ' == file_with_path ' . $file_with_path, LOG_OFF, __METHOD__ . '.' . __LINE__);
            }
        }
        $this->logIt("array of values: " . var_export($a_values, true), LOG_OFF);
        if ($template == '' || $a_values == array()) {
            $this->logIt("The Template or the array of values was empty", LOG_OFF);
            return false;
        }
        if (count($a_values) == 0) {
            $this->logIt("a_values was an empty array.", LOG_OFF);
            return false;
        }
        if ($is_file) {
            $tpl_content = $this->o_files->getContents($template, 'templates');
            if ($tpl_content == '') {
                $this->logIt("The template is empty: '{$template}'", LOG_OFF);
            } elseif ($tpl_content === false) {
                $this->logIt('file_get_contents failed', LOG_OFF);
            }
        }
        else {
            $tpl_content = $template;
        }
        if (($tpl_content !== false) && ($tpl_content != '')) {
            foreach ($a_values as $var_name=>$var_value) {
                $$var_name = $var_value;
                $this_var = "{\$" . $var_name . "}";
                $tpl_content = str_replace($this_var, $var_value, $tpl_content);
            }
            // now cleanup any vars without values
            $tpl_content = preg_replace('/{\$(.*)}/', '', $tpl_content);
        } else {
            $this->logIt('The template was empty.', LOG_OFF);
        }
        return $tpl_content;
    }

    ### UTILITIES ###
    /**
     * @param string $value
     */
    public function updateFilesNamespace($value = 'Ritc')
    {
        $this->o_files->setNamespace($value);
    }

    ### SETTERS ###
    /**
     * @param string $value
     */
    public function setNamespace($value = 'Ritc')
    {
        $this->namespace = $value;
        $this->updateFilesNamespace($value);
    }
}
