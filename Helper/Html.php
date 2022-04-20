<?php
/**
 * Class Html
 * @package Ritc_Library
 */
namespace Ritc\Library\Helper;

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
 * @change_log
 * - v1.0.6 - removed abstract class Base, used LogitTraits            - 09/01/2015 wer
 * - v1.0.5 - Refactored to match the Arrays class                     - 07/31/2015 wer
 * - v1.0.4 - moved to the Ritc\Library\Helper namespace               - 11/15/2014 wer
 * - v1.0.3 - changed to implment the changes in Base class            - 09/23/2014 wer
 * - v1.0.2 - some refactoring changes based on changes in package     - 12/19/2013 wer
 * - v1.0.1 - some refactoring changes based on changes in other files - 03/17/2013 wer
 */
class Html
{
    /** @var Arrays  */
    protected Arrays $o_arrays;
    /** @var Files  */
    protected Files $o_files;
    /** @var string  */
    protected string $the_original_string = 'Start';
    /** @var string */
    protected string $the_modified_string = '';
    /** @var string  */
    protected string $template_name = 'default.twig';
    /** @var string  */
    protected string $namespace = 'Ritc';

    /**
     * Html constructor.
     */
    public function __construct()
    {
        $this->o_arrays = new Arrays();
        $this->o_files  = new Files('main.twig', 'templates',  'default', 'Ritc');
    }

    /**
     * @param array $a_button_values
     * @return string
     */
    public function button(array $a_button_values = array()):string
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
        if ($a_button_values === []) {
            $a_button_values = $a_default_values;
        }
        if ($a_button_values['button_id'] === '' && $a_button_values['button_value'] !== '') {
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
    public function cssLink(string $css_file = '', string $css_dir = 'css', string $css_media = 'screen'):string
    {
        if($css_file === '') {
            return '';
        }
        $this->o_files->setFileDirName($css_dir);
        $a_tpl_values = array(
            'css_media'  => $css_media,
            'css_source' => $this->o_files->getFileWithDir($css_file));
        return $this->render('css.tpl', $a_tpl_values, true);
    }

    /**
     * @param string $message
     * @param string $image_file
     * @return string
     */
    public function failure(string $message = 'A Problem Has Occurred. Please Try Again.', string $image_file = ''):string
    {
        if ($image_file === '') {
            $image = $this->o_files->getImageWithDir('icons/failure.png');
        } else {
            $image = $this->o_files->getImageWithDir($image_file);
            if ($image === false) {
                $image = $this->o_files->getImageWithDir('icons/failure.png');
            }
        }
        $a_stuff = array('message' => $message, 'image'=>$image, 'class'=>'msg-failure', 'alt'=>'A Problem Has Occurred');
        return $this->render('message.tpl', $a_stuff, true);
    }

    /**
     * @param string $js_file
     * @param string $js_dir
     * @return string
     */
    public function jsLink(string $js_file = '', string $js_dir = 'js'):string
    {
        if($js_file === '') {
            return '';
        }
        $this->o_files->setFileDirName($js_dir);
        $a_tpl_values = array('js_source'=>$this->o_files->getFileWithDir($js_file));
        return $this->render('js.tpl', $a_tpl_values, true);
    }

    /**
     * Returns a formatted string for a success message.
     *
     * @param string $message
     * @param string $image_file
     * @return string
     */
    public function success(string $message = 'Success!', string $image_file = ''):string
    {
        if ($image_file === '') {
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
     *
     * @param string $message
     * @param string $image_file
     * @return string
     */
    public function warning(string $message = 'Warning!', string $image_file = ''):string
    {
        if ($image_file === '') {
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
     *
     * @param mixed|string $template may be the name of a file or a string. If
     *     a name of a file, the file must be in the templates directory
     *     of the in use theme. Suggestion is simple, if the same template
     *     is being used multiple time consecutively, put the template in
     *     a string once and pass it into the fill template method.
     * @param array        $a_values the values to insert into the template in
     *     an assoc array. $key is the string to find $value is the replacement.
     * @param bool         $is_file  the template is the path to a file, defaults to false
     * @return string - the filled in template
     */
    public function render(string $template = '', array $a_values = array(), bool $is_file = false):string
    {
        if ($template === '' || $a_values === []) {
            return false;
        }
        if (count($a_values) === 0) {
            return false;
        }
        if ($is_file) {
            $tpl_content = $this->o_files->getContents($template, 'templates');
        }
        else {
            $tpl_content = $template;
        }
        if (($tpl_content !== false) && ($tpl_content !== '')) {
            foreach ($a_values as $var_name=>$var_value) {
                $$var_name = $var_value;
                $this_var = '{$' . $var_name . '}';
                $tpl_content = str_replace($this_var, $var_value, $tpl_content);
            }
            // now cleanup any vars without values
            $tpl_content = preg_replace('/{\$(.*)}/', '', $tpl_content);
        }
        return $tpl_content;
    }

    ### UTILITIES ###
    /**
     * @param string $value
     */
    public function updateFilesNamespace(string $value = 'Ritc'):void
    {
        $this->o_files->setNamespace($value);
    }

    ### SETTERS ###
    /**
     * @param string $value
     */
    public function setNamespace(string $value = 'Ritc'):void
    {
        $this->namespace = $value;
        $this->updateFilesNamespace($value);
    }
}
