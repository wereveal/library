<?php
/**
 *  @brief Creates HTML strings.
 *  @details Methods which start with make_ return a modified version
 *  of the value passed into the method, usually indicated by
 *  the name of the method
 *  Derived from the old Depreciated Output class
 *  @file Html.php
 *  @ingroup ritc_library core
 *  @namespace Ritc/Library/Core
 *  @class Html
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.2
 *  @date 2013-12-19 08:29:54
 *  @note A part of the RITC Library v4
 *  @note <pre><b>Change Log</b>
 *      v1.0.2 - some refactoring changes based on changes in package - 12/19/2013 wer
 *      v1.0.1 - some refactoring changes based on changes in other files - 03/17/2013 wer
 *  </pre>
**/
namespace Ritc\Library\Core;

class Html extends namespace\Base
{
    protected $current_page;
    protected $o_arrays;
    protected $o_elog;
    protected $o_files;
    private   $o_str;
    protected $the_original_string = 'Start';
    protected $the_modified_string = '';
    protected $template_name     = STD_CONTENT_TPL;
    protected $private_properties;
    protected $namespace = 'Ritc';
    public function __construct()
    {
        $this->o_str = new namespace\Strings;
        $this->o_elog = namespace\Elog::start();
        $this->o_arrays = new namespace\Arrays();
        $this->o_files = new namespace\Files('main.tpl', 'templates',  'default', 'Ritc');
        $this->setPrivateProperties();
        $this->o_elog->setFromFile(__FILE__);
    }

    public function button($a_button_values = array())
    {
        $a_button_values = $this->o_arrays->stripUnspecifiedValues(
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
    public function cssLink($css_file = '', $css_dir = 'css', $css_media = 'screen')
    {
        if($css_file == '') {
            $this->o_elog->write('A Problem Has Occurred. The css file must be specified', LOG_OFF, __METHOD__ . '.' . __LINE__);
            return '';
        }
        $this->o_files->setFileDirName($css_dir);
        $a_tpl_values = array(
            'css_media'  => ($css_media),
            'css_source' => $this->o_files->getFileWithDir($css_file));
        return $this->render('css.tpl', $a_tpl_values, true);
    }
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
        $this->o_elog->write("Image w Dir: {$image}", LOG_OFF, __METHOD__ . '.' . __LINE__);
        $a_stuff = array('message' => $message, 'image'=>$image, 'class'=>'msg-failure', 'alt'=>'A Problem Has Occurred');
        return $this->render('message.tpl', $a_stuff, true);
    }
    public function jsLink($js_file = '', $js_dir = 'js')
    {
        if($js_file == '') {
            $this->o_elog->write('A Problem Has Occurred. The JavaScript File must be specified', LOG_OFF, __METHOD__ . '.' . __LINE__);
            return '';
        }
        $this->o_files->setFileDirName($js_dir);
        $a_tpl_values = array('js_source'=>$this->o_files->getFileWithDir($js_file));
        return $this->render('js.tpl', $a_tpl_values, true);
    }
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
     *  Fill the template with the values passed in.
     *  @param $template (mixed) may be the name of a file or a string. If
     *      a name of a file, the file must be in the templates directory
     *      of the in use theme. Suggestion is simple, if the same template
     *      is being used multiple time consecutively, put the template in
     *      a string once and pass it into the fill template method.
     *  @param $a_values (array) - the values to insert into the template in
     *      an assoc array. $key is the string to find $value is the replacement.
     *  @param $is_file (BOOL) - the template is the path to a file, defaults to false
     *  @return string - the filled in template
    **/
    public function render($template = '', $a_values = '', $is_file = false)
    {
        $this->o_elog->setFromMethod(__METHOD__ . '.' . __LINE__);
        if ($is_file) {
            $file_with_path = $this->o_files->getFileWithPath($template);
            if (file_exists($file_with_path)) {
                $this->o_elog->write("Template: " . $template . ' == file_with_path ' . $file_with_path, LOG_OFF);
            }
        }
        $this->o_elog->write("array of values: " . var_export($a_values, true), LOG_OFF);
        if ($template == '' || $a_values == '') {
            $this->o_elog->write("The Template or the array of values was empty", LOG_OFF);
            return false;
        }
        if (is_array($a_values) === false) {
            $this->o_elog->write('The var $a_values isn\'t an array: ' . var_export($a_values, true), LOG_OFF);
            return false;
        } elseif (count($a_values) == 0) {
            $this->o_elog->write("a_values was an empty array.", LOG_OFF);
            return false;
        }
        if ($is_file) {
            $tpl_content = $this->o_files->getContents($template, 'templates');
            if ($tpl_content == '') {
                $this->o_elog->write("The template is empty: '{$template}'", LOG_OFF);
            } elseif ($tpl_content === false) {
                $this->o_elog->write('file_get_contents failed', LOG_OFF);
            }
        } else {
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
            $this->o_elog->write('The template was empty.', LOG_OFF);
        }
        return $tpl_content;
    }

    ### UTILITIES ###
    public function updateFilesNamespace($value = 'Ritc')
    {
        $this->o_files->setNamespace($value);
    }

    ### SETTERS ###
    public function setNamespace($value = 'Ritc')
    {
        $this->namespace = $value;
        $this->updateFilesNamespace($value);
    }
}
