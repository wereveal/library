<?php
/**
 * Class FormHelper.
 * @package Ritc_Library
 */
namespace Ritc\Library\Helper;

/**
 * Helps build arrays used with Twig tpls..
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.0
 * @date    2018-06-07 13:57:26
 * @change_log
 * - v1.0.0-alpha.0 - Initial version        - 2018-06-07 wer
 * @todo FormHelper.php - Everything
 */
class FormHelper
{
    /**
     * Returns an array with all values needed for the single_button_form.twig tpl.
     * @param array $a_values
     * @return array
     */
    public static function singleBtnForm(array $a_values = [])
    {
        $form_action = empty($a_values['form_action'])
            ? '/'
            : $a_values['form_action'];
        $form_class = empty($a_values['form_class'])
            ? ''
            : $a_values['form_class'];
        $btn_value = empty($a_values['btn_value'])
            ? 'save'
            : $a_values['btn_value'];
        $btn_color = empty($a_values['btn_color'])
            ? 'btn-primary'
            : $a_values['btn_color'];
        $btn_size = empty($a_values['btn_size'])
            ? ''
            : $a_values['btn_size'];
        $btn_label = empty($a_values['btn_label'])
            ? 'Save'
            : $a_values['btn_label'];
        $tolken = empty($a_values['tolken'])
            ? $_SESSION['token']
            : $a_values['tolken'];
        $form_ts = empty($a_values['form_ts'])
            ? $_SESSION['idle_timestamp']
            : $a_values['form_ts'];
        $hidden_name = empty($a_values['hidden_name'])
            ? ''
            : $a_values['hidden_name'];
        $hidden_value = empty($a_values['hidden_value'])
            ? ''
            : $a_values['hidden_value'];
        return [
            'form_action'  => $form_action,
            'form_class'   => $form_class,
            'btn_value'    => $btn_value,
            'btn_color'    => $btn_color,
            'btn_size'     => $btn_size,
            'btn_label'    => $btn_label,
            'tolken'       => $tolken,
            'form_ts'      => $form_ts,
            'hidden_name'  => $hidden_name,
            'hidden_value' => $hidden_value
        ];
    }

    /**
     * Makes required key => value pairs for the checkbox.twig tpl.
     * @param array $a_values
     * @return array
     */
    public static function checkbox(array $a_values = [])
    {
        if (empty($a_values['id']) || empty($a_values['name']) || empty($a_values['label'])) {
            return [];
        }
        $value = empty($a_values['value'])
            ? 'true'
            : $a_values['value'];
        $checked = empty($a_values['checked'])
            ? ''
            : $a_values['checked'];
        return [
            'id'      => $a_values['id'],
            'name'    => $a_values['name'],
            'value'   => $value,
            'checked' => $checked,
            'label'   => $a_values['label']
        ];
    }
}
