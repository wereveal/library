<?php
/**
 * Class NavgroupsView.
 * @package Ritc_Library
 */
namespace Ritc\Library\Views;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ViewInterface;
use Ritc\Library\Models\NavgroupsModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigViewTraits;

/**
 * View for the Navgroups Manager.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.0+
 * @date    2018-06-19 12:11:51
 * @change_log
 * - 1.0.0-alpha.0 - Initial version                            - 2018-06-19 wer
 * @todo NavgroupsView.php - Everything
 */
class NavgroupsView implements ViewInterface
{
    use ConfigViewTraits;

    /**
     * NavgroupsView constructor.
     *
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupView($o_di);
    }

    /**
     * Default method for rendering the html.
     *
     * @param array $a_message
     * @return string
     */
    public function render(array $a_message = []):string
    {
        $o_ng_db = new NavgroupsModel($this->o_db);
        try {
            $a_groups = $o_ng_db->read();
        }
        catch (ModelException $e) {
            $a_groups = [];
            $a_message = ViewHelper::errorMessage("Could not retrieve the navgroups:<br>" . $e->errorMessage());
        }
        $a_twig_values = $this->createDefaultTwigValues($a_message, '/manager/config/navgroups/');
        foreach ($a_groups as $key => $a_group) {
            $a_groups[$key]['active_btn']  = [
                'id'      => 'ng_active' . $a_group['ng_id'],
                'name'    => 'ng_active',
                'value'   => 'true',
                'checked' => $a_group['ng_active'] === 'true' ? ' checked' : '',
                'label'   => 'Active'
            ];
            $a_groups[$key]['default_btn'] = [
                'id'      => 'ng_default' . $a_group['ng_id'],
                'name'    => 'ng_default',
                'value'   => 'true',
                'checked' => $a_group['ng_default'] === 'true' ? ' checked' : '',
                'label'   => 'Default'
            ];
            $a_groups[$key]['immutable_btn'] = [
                'id'      => 'ng_immutable' . $a_group['ng_id'],
                'name'    => 'ng_immutable',
                'value'   => 'true',
                'checked' => $a_group['ng_immutable'] === 'true' ? ' checked' : '',
                'label'   => 'Immutable'
            ];
            $a_groups[$key]['action_btns'] = [
                'btn_update_color' => 'btn-green',
                'btn_delete_color' => 'btn-red',
                'btn_update_size'  => 'btn-sm',
                'btn_delete_size'  => 'btn-sm',
                'hidden_name'      => 'ng_id',
                'hidden_value'     => $a_group['ng_id']
            ];
        }
        $a_twig_values['a_ng'] = $a_groups;
        $a_twig_values['blank_active'] = [
            'id'      => 'ng_active',
            'name'    => 'ng_active',
            'value'   => 'true',
            'checked' => '',
            'label'   => 'Active'
        ];
        $a_twig_values['blank_default'] = [
            'id'      => 'ng_default',
            'name'    => 'ng_default',
            'value'   => 'true',
            'checked' => '',
            'label'   => 'Default'
        ];
        $a_twig_values['blank_immutable'] = [
            'id'      => 'ng_immutable',
            'name'    => 'ng_immutable',
            'value'   => 'true',
            'checked' => '',
            'label'   => 'Immutable'
        ];
        $a_twig_values['blank_new'] = [
            'btn_primary' => 'btn-green',
            'btn_size'    => 'btn-sm'
        ];
        $a_twig_values['blank_active_b']           = $a_twig_values['blank_active'];
        $a_twig_values['blank_active_b']['id']    .= '-b';
        $a_twig_values['blank_default_b']          = $a_twig_values['blank_default'];
        $a_twig_values['blank_default_b']['id']   .= '-b';
        $a_twig_values['blank_immutable_b']        = $a_twig_values['blank_immutable'];
        $a_twig_values['blank_immutable_b']['id'] .= '-b';

        $tpl = $this->createTplString($a_twig_values);
        return $this->renderIt($tpl, $a_twig_values);
    }
}
