<?php
/**
 * Class NavigationView
 * @package Ritc_Library
 */
namespace Ritc\Library\Views;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\FormHelper;
use Ritc\Library\Models\NavComplexModel;
use Ritc\Library\Models\NavgroupsModel;
use Ritc\Library\Models\UrlsModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigViewTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * The view class for the navigation manager.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-alpha.2
 * @date    2017-06-19 13:11:34
 * @change_log
 * - v1.0.0-alpha.2 - refactoring in model   - 2017-06-19 wer
 * - v1.0.0-alpha.1 - Name refactoring       - 2017-05-14 wer
 * - v1.0.0-alpha.0 - Initial version        - 2016-04-15 wer
 */
class NavigationView
{
    use LogitTraits, ConfigViewTraits;

    /**
     * @var \Ritc\Library\Models\NavComplexModel
     */
    private $o_nav_complex;

    /**
     * NavigationView constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupElog($o_di);
        $this->setupView($o_di);
        $this->o_nav_complex = new NavComplexModel($o_di);
    }

    /**
     * Returns html, the list of navigation record forms.
     * @param array $a_message
     * @return string
     */
    public function renderList(array $a_message = [])
    {
        $meth = __METHOD__ . '.';
        $a_twig_values = $this->createDefaultTwigValues($a_message, '/manager/config/navigation/');
        try {
            $a_nav = $this->o_nav_complex->readAllNavUrlTree();
        }
        catch (ModelException $e) {
            $a_nav = [];
        }
        $log_message = 'a_nav' . var_export($a_nav, true);
        $this->logIt($log_message, LOG_ON, $meth . __LINE__);

        $a_twig_values['a_nav'] = $a_nav;
        $a_navgroups_btn = [
            'url'   => PUBLIC_DIR . '/manager/config/navgroups/',
            'class' => 'btn btn-primary',
            'text'  => 'Navgroups'
        ];
        $a_twig_values['navgroups_btn'] = $a_navgroups_btn;
        $a_twig_values['new_btn_form'] = FormHelper::singleBtnForm([
            'form_action'  => PUBLIC_DIR . '/manager/config/navigation/',
            'btn_value'    => 'new',
            'btn_label'    => 'New',
            'btn_color'    => 'btn-primary',
            'btn_size'     => 'btn-xs',
            'hidden_name'  => 'nav_id',
            'hidden_value' => ''
        ]);
        $tpl = $this->createTplString($a_twig_values);
        return $this->renderIt($tpl, $a_twig_values);
    }

    /**
     * Returns html, a form to add/modify a navigation record.
     * @param int $nav_id Required.
     * @return string
     */
    public function renderForm($nav_id = -1)
    {
        $meth = __METHOD__ . '.';
        $a_twig_values = $this->createDefaultTwigValues();
        if ($nav_id == -1) {
            $a_twig_values['action'] = 'save';
            $a_twig_values['a_url_select']['select'] = $this->createUrlSelect($nav_id);
            $a_twig_values['a_nav_select']['select'] = $this->createNavSelect($nav_id);
            $a_twig_values['a_ng_select']['select']  = $this->createNgSelect($nav_id);
            $a_twig_values['a_nav_lvl_select']['select'] = $this->createNavLvlSelect(0);
            $a_chbx_values = [
                'id'      => 'nav_active',
                'name'    => 'nav_active',
                'label'   => 'Active'
            ];
            $a_twig_values['nav_active_ckbx'] = FormHelper::checkbox($a_chbx_values);
        }
        else {
            try {
                $results = $this->o_nav_complex->getNavRecord($nav_id);
            }
            catch (ModelException $e) {
                $results = [];
            }
            foreach ($results as $nav_label => $nav_value) {
                switch ($nav_label) {
                    case 'nav_active':
                        $a_chbx_values = [
                            'id'      => 'nav_active',
                            'name'    => 'nav_active',
                            'label'   => 'Active',
                            'checked' => $nav_value == 'true' ? ' checked' : ''
                        ];
                        $a_twig_values['nav_active_ckbx'] = FormHelper::checkbox($a_chbx_values);
                        break;
                    case 'url_id':
                        $a_twig_values['a_url_select']['select'] = $this->createUrlSelect($nav_value);
                        break;
                    case 'nav_parent_id':
                        $a_twig_values['a_nav_select']['select'] = $this->createNavSelect($nav_value);
                        break;
                    case 'nav_level':
                        $a_twig_values['a_nav_lvl_select']['select'] = $this->createNavLvlSelect($nav_value);
                        break;
                    default:
                        $a_twig_values[$nav_label] = $nav_value;
                }
            }
            $a_twig_values['action'] = 'modify';
        }
        $log_message = 'twig values ' . var_export($a_twig_values, TRUE);
        $this->logIt($log_message, LOG_ON, $meth . __LINE__);
        $a_twig_values['tpl'] = 'navigation_form';
        $tpl = $this->createTplString($a_twig_values);
        return $this->renderIt($tpl, $a_twig_values);
    }

    /**
     * Creates an array for the twig values to display a select listing the urls.
     * @param int $url_id
     * @return array
     */
    private function createUrlSelect($url_id = -1)
    {
        $a_select = [
            'id'          => '',
            'label_class' => '',
            'label_text'  => '',
            'name'        => 'url_id',
            'class'       => 'form-control',
            'other_stuph' => '',
            'options'     => []
        ];
        $o_urls = new UrlsModel($this->o_db);
        try {
            $a_results = $o_urls->read();
            $a_options = [[
                'value'       => 0,
                'label'       => '--Select Url--',
                'other_stuph' => $url_id == -1 ? ' selected' : ''
            ]];
            foreach ($a_results as $url) {
                $a_temp = [
                    'value'       => $url['url_id'],
                    'label'       => $url['url_text'],
                    'other_stuph' => ''
                ];
                if ($url['url_id'] == $url_id) {
                    $a_temp['other_stuph'] = ' selected';
                }
                if ($a_temp['value'] != '') {
                    $a_options[] = $a_temp;
                }
            }
            $a_select['options'] = $a_options;
            return $a_select;
        }
        catch (ModelException $e) {
            return [];
        }
    }

    /**
     * Creates an array for the twig values to display a select listing the navigation records.
     * @param int $nav_id
     * @return array
     */
    private function createNavSelect($nav_id = -1)
    {
        try {
            $a_nav = $this->o_nav_complex->readAllNavUrlTree();
        }
        catch (ModelException $e) {
            $a_nav = [];
        }

        $a_select = [
            'id'   => '',
            'label_class' => '',
            'label_text'  => '',
            'name'        => 'nav_parent_id',
            'class'       => 'form-control',
            'other_stuph' => '',
            'options'     => []
        ];
        $a_options = [
            [
                'value'       => 0,
                'label'       => '--Select Navigation--',
                'other_stuph' => $nav_id == -1 ? ' selected' : ''
            ],
        ];
        $a_options[] = [
            'value'       => $nav_id > 0 ? $nav_id : -1,
            'label'       => 'SELF',
            'other_stuph' => ''
        ];
        foreach ($a_nav as $nav) {
            $a_temp = [
                'value'       => $nav['nav_id'],
                'label'       => $nav['nav_text'] . ' (level ' . $nav['nav_level'] . ')',
                'other_stuph' => ''
            ];
            if ($nav['nav_id'] == $nav_id) {
                $a_temp['other_stuph'] = ' selected';
            }
            $a_options[] = $a_temp;
            if (!empty($nav['submenu'])) {
                foreach ($nav['submenu'] as $submenu) {
                    $a_temp = [
                        'value'       => $submenu['nav_id'],
                        'label'       => $submenu['nav_text'] . ' (level ' . $submenu['nav_level'] . ')',
                        'other_stuph' => ''
                    ];
                    if ($submenu['nav_id'] == $nav_id) {
                        $a_temp['other_stuph'] = ' selected';
                    }
                    $a_options[] = $a_temp;
                    if (!empty($submenu['submenu'])) {
                        foreach ($submenu['submenu'] as $ssmenu) {
                            $a_temp = [
                                'value'       => $ssmenu['nav_id'],
                                'label'       => $ssmenu['nav_text'] . ' (level ' . $ssmenu['nav_level'] . ')',
                                'other_stuph' => ''
                            ];
                            if ($ssmenu['nav_id'] == $nav_id) {
                                $a_temp['other_stuph'] = ' selected';
                            }
                            $a_options[] = $a_temp;
                        }
                    }
                }
            }
        }
        $a_select['options'] = $a_options;
        return $a_select;
    }

    /**
     * Creates the array for the select template.
     *
     * @param int $nav_level
     * @return array
     */
    private function createNavLvlSelect($nav_level = 0)
    {
        $a_options = [[
            'value'       => 0,
            'label'       => '--Select Nav Level--',
            'other_stuph' => $nav_level === 0 ? ' selected' : ''
        ]];
        for ($x = 1; $x <= 3; $x++) {
            $a_options[] = [
                'value'       => $x,
                'label'       => 'Nav Level ' . $x,
                'other_stuph' => $nav_level === $x ? ' selected' : ''
            ];
        }
        return [
            'id'          => '',
            'label_class' => '',
            'label_text'  => '',
            'name'        => 'nav_level',
            'class'       => 'form-control',
            'other_stuph' => '',
            'options'     => $a_options
        ];
    }

    /**
     * Creates an array for the twig values to display a select for the navigation group.
     * @param int $parent_ng_id The parent navigation group id
     * @return array
     */
    private function createNgSelect($parent_ng_id = -1)
    {
        $o_ng = new NavgroupsModel($this->o_db);
        try {
            $results = $o_ng->read();
        }
        catch (ModelException $e) {
            $results = [];
        }

        $a_select = [
            'id'          => '',
            'label_class' => '',
            'label_text'  => '',
            'name'        => 'ng_id',
            'class'       => 'form-control',
            'other_stuph' => '',
            'options'     => []
        ];
        $a_options = [[
            'value'       => 0,
            'label'       => '--Select NavGroup--',
            'other_stuph' => $parent_ng_id == -1 ? ' selected' : ''
        ]];
        foreach($results as $ng) {
            $a_temp = [
                'value'       => $ng['ng_id'],
                'label'       => $ng['ng_name'],
                'other_stuph' => ''
            ];
            if ($parent_ng_id != -1 && $ng['ng_id'] == $parent_ng_id) {
                $a_temp['other_stuph'] = ' selected';
            }
            $a_options[] = $a_temp;
        }
        $a_select['options'] = $a_options;
        return $a_select;
    }

    /**
     * Creates the array used in the twig template to display the list of navigation records.
     * @param array $a_values
     * @return array
     */
    private function createTwigListArray(array $a_values = [])
    {
        $a_twig_nav = [];
        foreach ($a_values as $nav) {
            $submenu = [];
            if (!empty($nav['submenu'])) {
                $submenu = $this->createTwigListArray($nav['submenu']);
            }
            $a_twig_nav[] = [
                'nav_text'        => $nav['nav_text'],
                'nav_description' => $nav['nav_description'],
                'form_action'     => PUBLIC_DIR . '/manger/config/navigation/',
                'form_class'      => '',
                'btn_primary'     => 'btn-primary',
                'btn_danger'      => 'btn-danger',
                'hidden_name'     => 'nav_id',
                'hidden_value'    => $nav['nav_id'],
                'submenu'         => $submenu

            ];
        }
        return $a_twig_nav;
    }
}
