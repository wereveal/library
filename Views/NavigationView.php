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

/**
 * The view class for the navigation manager.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 2.0.0
 * @date    2021-12-01 13:17:21
 * @change_log
 * - 2.0.0          - updated for php 8 standards               - 2021-12-01 wer
 * - 1.0.0          - bug fixes                                 - 2020-08-24 wer
 * - 1.0.0-alpha.2  - refactoring in model                      - 2017-06-19 wer
 * - 1.0.0-alpha.0  - Initial version                           - 2016-04-15 wer
 */
class NavigationView
{
    use ConfigViewTraits;

    /** @var NavComplexModel */
    private NavComplexModel $o_nav_complex;

    /**
     * NavigationView constructor.
     *
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupView($o_di);
        $this->o_nav_complex = new NavComplexModel($o_di);
    }

    /**
     * Returns html, the list of navigation record forms.
     *
     * @param array $a_message
     * @return string
     */
    public function renderList(array $a_message = []):string
    {
        $a_nav = $this->getNavUrlTree();

        $a_twig_values = $this->createDefaultTwigValues($a_message, '/manager/config/navigation/');
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
     *
     * @param int $nav_id Required.
     * @return string
     */
    public function renderForm(int $nav_id = -1):string
    {
        $a_twig_values = $this->createDefaultTwigValues();
        $a_nav = [];
        if ($nav_id === -1) {
            $cache_key = 'nav.form.by.new';
            if ($this->use_cache) {
                $a_nav = $this->o_cache->get($cache_key);
            }
            if (empty($a_nav)) {
                $a_nav['a_url_select']     = ['select' => $this->createUrlSelect()];
                $a_nav['a_nav_select']     = ['select' => $this->createNavSelect()];
                $a_nav['a_ng_select']      = ['select' => $this->createNgSelect()];
                $a_nav['a_nav_lvl_select'] = ['select' => $this->createNavLvlSelect()];
                $a_chbx_values = [
                    'id'      => 'nav_active',
                    'name'    => 'nav_active',
                    'label'   => 'Active'
                ];
                $a_nav['nav_active_ckbx'] = FormHelper::checkbox($a_chbx_values);
                $a_nav['nav_id'] = '';
                if ($this->use_cache) {
                    $this->o_cache->set($cache_key, $a_nav, 'nav');
                }
            }
        }
        else {
            $cache_key = 'nav.form.by.id.' . $nav_id;
            if ($this->use_cache) {
                $a_nav = $this->o_cache->get($cache_key);
            }
            if (empty($a_nav)) {
                try {
                    $results = $this->o_nav_complex->getNavRecord($nav_id);
                }
                catch (ModelException) {
                    $results = [];
                }
                $a_nav['nav_active_ckbx']  = [];
                $a_nav['a_url_select']     = ['select' => []];
                $a_nav['a_nav_select']     = ['select' => []];
                $a_nav['a_nav_lvl_select'] = ['select' => []];
                $a_nav['a_ng_select']      = ['select' => []];
                foreach ($results as $nav_label => $nav_value) {
                    switch ($nav_label) {
                        case 'nav_active':
                            $a_chbx_values = [
                                'id'      => 'nav_active',
                                'name'    => 'nav_active',
                                'label'   => 'Active',
                                'checked' => $nav_value === 'true' ? ' checked' : ''
                            ];
                            $a_nav['nav_active_ckbx'] = FormHelper::checkbox($a_chbx_values);
                            break;
                        case 'url_id':
                            $a_nav['a_url_select'] = ['select' => $this->createUrlSelect($nav_value)];
                            break;
                        case 'parent_id':
                            $a_nav['a_nav_select'] = ['select' => $this->createNavSelect($nav_value)];
                            break;
                        case 'ng_id':
                            $a_nav['a_ng_select']  = ['select' => $this->createNgSelect($nav_value)];
                            break;
                        case 'nav_level':
                            $a_nav['a_nav_lvl_select'] = ['select' => $this->createNavLvlSelect($nav_value)];
                            break;
                        default:
                            $a_nav[$nav_label] = $nav_value;
                    }
                }
                if ($this->use_cache) {
                    $this->o_cache->set($cache_key, $a_nav, 'nav');
                }
            }
        }
        $a_twig_values['nav'] = $a_nav;
        $a_twig_values['tpl'] = 'navigation_form';
        $tpl = $this->createTplString($a_twig_values);
        return $this->renderIt($tpl, $a_twig_values);
    }

    /**
     * Creates an array for the twig values to display a select listing the urls.
     *
     * @param int $url_id      Optional, both $url_id and $navgroup_id need to be set if either is.
     * @param int $navgroup_id Optional, both $url_id and $navgroup_id need to be set if either is.
     * @return array
     */
    private function createUrlSelect(int $url_id = -1, int $navgroup_id = -1):array
    {
        $a_select = [
            'id'          => 'url_id',
            'label_class' => '',
            'label_text'  => '',
            'name'        => 'url_id',
            'class'       => 'form-control',
            'other_stuph' => '',
            'options'     => []
        ];
        if ($url_id < 1 || $navgroup_id < -1) {
            $a_options = [
                [
                    'value'       => 0,
                    'label'       => '--Select a Navgroup First--',
                    'other_stuph' => ' selected'
                ]
            ];
            $a_select['options'] = $a_options;
            return $a_select;
        }
        $cache_key = 'nav.select.urls.' . $url_id . '.for.' . $navgroup_id;
        $results = '';
        if ($this->use_cache) {
            $results = $this->o_cache->get($cache_key);
        }
        if (empty($results)) {
            $o_urls = new UrlsModel($this->o_db);
            try {
                $a_results = $o_urls->readNotInNavgroup($navgroup_id);
                $a_options = [
                    [
                        'value'       => 0,
                        'label'       => '--Select Url--',
                        'other_stuph' => ''
                    ]
                ];
                foreach ($a_results as $url) {
                    $a_temp = [
                        'value'       => $url['url_id'],
                        'label'       => $url['url_text'],
                        'other_stuph' => ''
                    ];
                    if ($url['url_id'] === $url_id) {
                        $a_temp['other_stuph'] = ' selected';
                    }
                    if (!empty($a_temp['value'])) {
                        $a_options[] = $a_temp;
                    }
                }
                $a_select['options'] = $a_options;
                if ($this->use_cache) {
                    $this->o_cache->set($cache_key, $a_select, 'nav');
                }
                return $a_select;
            }
            catch (ModelException) {
                return [];
            }
        }
        else {
            return $results;
        }
    }

    /**
     * Creates an array for the twig values to display a select listing the navigation records.
     *
     * @param int $nav_id
     * @return array
     */
    private function createNavSelect(int $nav_id = -1):array
    {
        $a_nav = $this->getNavUrlTree();
        $a_select = [
            'id'          => 'parent_id',
            'label_class' => '',
            'label_text'  => '',
            'name'        => 'parent_id',
            'class'       => 'form-control',
            'other_stuph' => ''
        ];
        $a_options = [
            [
                'value'       => 0,
                'label'       => '--Select Parent--',
                'other_stuph' => $nav_id === -1 ? ' selected' : ''
            ]
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
            if ($nav['nav_id'] === $nav_id) {
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
                    if ($submenu['nav_id'] === $nav_id) {
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
                            if ($ssmenu['nav_id'] === $nav_id) {
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
    private function createNavLvlSelect(int $nav_level = 0):array
    {
        $a_options = [[
            'value'       => 0,
            'label'       => '--Select Nav Level--',
            'other_stuph' => $nav_level === 0 ? ' selected' : ''
        ]];
        for ($x = 1; $x <= 3; $x++) {
            $other_stuph = $nav_level === $x ? ' selected' : '';
            $a_options[] = [
                'value'       => $x,
                'label'       => 'Nav Level ' . $x,
                'other_stuph' => $other_stuph
            ];
        }
        return [
            'id'          => 'nav_level',
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
     *
     * @param int $ng_id Optional, the navigation group id
     * @return array
     */
    private function createNgSelect(int $ng_id = -1):array
    {
        $cache_key = 'nav.select.navgroups';
        $results = '';
        if ($this->use_cache) {
            $results = $this->o_cache->get($cache_key);
        }
        if (empty($results)) {
            $o_ng = new NavgroupsModel($this->o_db);
            try {
                $results = $o_ng->read();
                if ($this->use_cache) {
                    $this->o_cache->set($cache_key, $results, 'nav');
                }
            }
            catch (ModelException) {
                $results = [];
            }
        }
        $a_select = [
            'id'          => 'ng_id',
            'label_class' => '',
            'label_text'  => '',
            'name'        => 'ng_id',
            'class'       => 'form-control',
            'other_stuph' => ' onchange="urlsForNavgroup(this)"'
        ];
        $a_options = [[
            'value'       => 0,
            'label'       => '--Select NavGroup--',
            'other_stuph' => $ng_id === -1 ? ' selected' : ''
        ]];
        foreach($results as $ng) {
            $a_temp = [
                'value'       => $ng['ng_id'],
                'label'       => $ng['ng_name'],
                'other_stuph' => ''
            ];
            if ($ng_id !== -1 && $ng['ng_id'] === $ng_id) {
                $a_temp['other_stuph'] = ' selected';
            }
            $a_options[] = $a_temp;
        }
        $a_select['options'] = $a_options;
        return $a_select;
    }

    /**
     * Creates the array used in the twig template to display the list of navigation records.
     *
     * @param array $a_values
     * @return array
     */
    public function createTwigListArray(array $a_values = []):array
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

    /**
     * Returns the nav url list.
     *
     * @return array
     */
    private function getNavUrlTree():array
    {
        $a_nav = [];
        $cache_key = 'nav.url.tree';
        if ($this->use_cache) {
            $a_nav = $this->o_cache->get($cache_key);
        }
        if (empty($a_nav)) {
            try {
                $a_nav = $this->o_nav_complex->readAllNavUrlTree();
                if ($this->use_cache) {
                    $this->o_cache->set($cache_key, $a_nav, 'nav');
                }
            }
            catch (ModelException) {
                $a_nav = [];
            }
        }
        return $a_nav;
    }
}
