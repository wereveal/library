<?php
/**
 * Class RoutesGroupView
 * @package Ritc_Library
 */
namespace Ritc\Library\Views;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ViewInterface;
use Ritc\Library\Models\GroupsModel;
use Ritc\Library\Models\RoutesComplexModel;
use Ritc\Library\Models\RoutesGroupMapModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigViewTraits;

/**
 * View for Route Group mapping admin.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.0
 * @date    2017-05-14 16:38:08
 * @change_log
 * - v1.0.0-alpha.0 - Initial version        - 2017-05-14 wer
 * @todo Ritc/Library/Views/RoutesGroupView.php - Everything
 */
class RoutesGroupView implements ViewInterface
{
    use ConfigViewTraits;

    /**
     * RoutesGroupView constructor.
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupView($o_di);
    }

    /**
     * Main method to render the html.
     *
     * @return string
     */
    public function render():string
    {
        $o_map = new RoutesGroupMapModel($this->o_db);
        /** @var array $a_route_select */
        $a_route_select = $this->makeRoutesSelect();
        /** @var array $a_group_select */
        $a_group_select = $this->makeGroupsSelect();
        $a_message      = [];
        try {
            $a_maps = $o_map->read();
            foreach ($a_maps as $key => $value) {
                $a_route_options = $a_route_select['options'];
                foreach ($a_route_options as $op_key => $op_value) {
                    if ($op_value['value'] === $value['route_id']) {
                        $a_route_options[$op_key]['other_stuph'] = ' selected';
                    }
                }
                $a_group_options = $a_group_select['options'];
                foreach ($a_group_options as $gr_key => $gr_value) {
                    if ($gr_value['value'] === $value['group_id']) {
                        $a_group_options[$gr_key]['other_stuph'] = ' selected';
                    }
                }
                $a_maps[$key]['route_select'] = $a_route_select;
                $a_maps[$key]['group_select'] = $a_group_select;
            }
        }
        catch (ModelException $e) {
            $message = 'A problem occurred and the map was not able to be returned.';
            if (DEVELOPER_MODE) {
                $message .= ' - ' . $e->errorMessage();
            }
            $a_message = ViewHelper::errorMessage($message);
        }
        $a_twig_values = $this->createDefaultTwigValues($a_message);
        $a_route_select[0]['other_stuph'] = ' selected';
        $a_twig_values['route_select'] = $a_route_select;
        $a_group_select[0]['other_stuph'] = ' selected';
        $a_twig_values['group_select'] = $a_group_select;
        $tpl = $this->createTplString($a_twig_values);
        return $this->renderIt($tpl, $a_twig_values);
    }

    /**
     * Creates an arry usable by the twig template to create a <select> for routes.
     *
     * @return array
     */
    public function makeRoutesSelect(): array
    {
        $a_return_this = [
            'id'          => 'routes',
            'label_class' => '',
            'label_text'  => 'Routes',
            'name'        => 'routes',
            'class'       => '',
            'other_stuph' => ''
        ];
        $a_options     = [];
        $a_rg_data = $this->getRGData();
        foreach ($a_rg_data as $a_rg_datum) {
            $a_options[] = [
                'value'       => $a_rg_datum['route_id'],
                'label'       => $a_rg_datum['url'],
                'other_stuph' => ''
            ];
        }
        $a_return_this['options'] = $a_options;
        return $a_return_this;
    }

    /**
     * Creates an arry usable by the twig template to create a <select> for groups.
     *
     * @return array
     */
    public function makeGroupsSelect(): array
    {
        $a_return_this = [
            'id'          => 'groups',
            'label_class' => '',
            'label_text'  => 'Groups',
            'name'        => 'groups',
            'class'       => '',
            'other_stuph' => ''
        ];
        $a_options = [];
        $o_groups = new GroupsModel($this->o_db);
        try {
            $a_groups = $o_groups->read();
        }
        catch (ModelException) {
            $a_return_this['options'] = $a_options;
            return $a_return_this;
        }
        foreach ($a_groups as $a_group) {
            $a_options[] = [
                'value'       => $a_group['group_id'],
                'label'       => $a_group['group_name'],
                'other_stuph' => ''
            ];
        }
        $a_return_this['options'] = $a_options;
        return $a_return_this;
    }

    /**
     * @return array
     */
    public function getRGData(): array
    {
        $o_routes = new RoutesComplexModel($this->o_di);
        try {
            $a_routes = $o_routes->readAll();
        }
        catch (ModelException) {
            return [];
        }
        $a_return_this = [];
        foreach ($a_routes as $a_route) {
            $a_return_this[] = [
                'route_id' => $a_route['route_id'],
                'url'      => $a_route['url_text']
            ];
        }
        return $a_return_this;
    }
}
