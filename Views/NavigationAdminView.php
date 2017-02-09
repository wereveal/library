<?php
/**
 * @brief     The view class for the navigation manager.
 * @ingroup   lib_views
 * @file      NavigationAdminView.php
 * @namespace Ritc\Library\Views
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.0
 * @date      2016-04-15 12:16:48
 * @note Change Log
 * - v1.0.0-alpha.0 - Initial version        - 2016-04-15 wer
 * @todo NavigationAdminView.php - Everything
 */
namespace Ritc\Library\Views;

use Ritc\Library\Models\NavComplexModel;
use Ritc\Library\Models\NavgroupsModel;
use Ritc\Library\Models\UrlsModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Traits\ViewTraits;

/**
 * Class NavigationAdminView.
 * @class   NavigationAdminView
 * @package Ritc\Library\Views
 */
class NavigationAdminView
{
    use LogitTraits, ViewTraits;

    private $o_nav_complex;
    private $o_nav_model;

    public function __construct(Di $o_di)
    {
        $this->setupElog($o_di);
        $this->setupView($o_di);
        $this->o_nav_model = new NavgroupsModel($this->o_db);
        $this->o_nav_complex = new NavComplexModel($this->o_db);
        if (defined('DEVELOPER_MODE') && DEVELOPER_MODE) {
            $this->o_nav_model->setElog($this->o_elog);
            $this->o_nav_complex->setElog($this->o_elog);
        }
    }

    /**
     * Returns html, the list of navigation record forms.
     * @param array $a_message
     * @return string
     */
    public function renderList(array $a_message = [])
    {
        $meth = __METHOD__ . '.';
        $a_twig_values = $this->createDefaultTwigValues('library', $a_message);
        $a_twig_values['page_title'] = 'Manager for Navigation Values';

        $a_nav = $this->o_nav_complex->getNavListAll();
        $a_nav = $this->createSubmenu($a_nav);
        $a_nav = $this->sortTopLevel($a_nav);

        $log_message = 'Search Results ' . var_export($a_nav, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        $a_twig_values['a_nav'] = $a_nav;

        $a_twig_values['new_btn_form'] = [
            'form_action'  => PUBLIC_DIR . '/manager/library/navigation/new/',
            'form_class'   => '',
            'btn_value'    => 'new_page',
            'btn_label'    => 'New Navigation',
            'btn_color'    => 'btn-primary',
            'btn_size'     => 'btn-xs',
            'hidden_name'  => 'nav_id',
            'hidden_value' => ''
        ];
        $a_twig_values['modify_btn_form'] = [
            'form_action'  => PUBLIC_DIR . '/manager/library/navigation/modify/',
            'form_class'   => '',
            'btn_value'    => 'modify_nav',
            'btn_label'    => 'Modify/Delete',
            'btn_color'    => 'btn-primary',
            'btn_size'     => 'btn-xs',
            'hidden_name'  => 'nav_id'
        ];
        $tpl = '@' . LIB_TWIG_PREFIX . 'pages/nav_admin.twig';
        return $this->o_twig->render($tpl, $a_twig_values);
    }

    /**
     * Returns html, a form to add/modify a navigation record.
     * @param int $nav_id Required.
     * @return string
     */
    public function renderForm($nav_id = -1)
    {
        $meth = __METHOD__ . '.';
        $a_twig_values = $this->createDefaultTwigValues('library');
        /* Additional twig_values needed
         * action
         * a_uls_select['select']
         * a_nav_select['select']
         * selected0 - selected3
         */
        if ($nav_id == -1) {
            $a_twig_values['action'] = 'save';
            $a_twig_values['a_url_select']['select'] = $this->createUrlSelect($nav_id);
            $a_twig_values['a_nav_select']['select'] = $this->createNavSelect($nav_id);
            $a_twig_values['a_ng_select']['select']  = $this->createNgSelect($nav_id);
            $a_twig_values['selected0'] = ' selected';
        }
        else {
            $results = $this->o_nav_complex->getNavRecord($nav_id);
            foreach ($results as $nav_label => $nav_value) {
                switch ($nav_label) {
                    case 'nav_active':
                        $a_twig_values['active_checked'] = $nav_value == 1 ? ' checked' : '';
                        break;
                    case 'url_id':
                        $a_twig_values['a_url_select']['select'] = $this->createUrlSelect($nav_value);
                        break;
                    case 'nav_parent_id':
                        $a_twig_values['a_nav_select']['select'] = $this->createNavSelect($nav_value);
                        break;
                    case 'nav_level':
                        $a_twig_values['selected' . $nav_value] = ' selected';
                        break;
                    default:
                        $a_twig_values[$nav_label] = $nav_value;
                }
            }
            $a_twig_values['action'] = 'modify';
        }
        $log_message = 'twig values ' . var_export($a_twig_values, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        $tpl = '@' . LIB_TWIG_PREFIX . 'pages/nav_admin_form.twig';
        return $this->o_twig->render($tpl, $a_twig_values);
    }

    /**
     * Returns html, a form to verify the manager wants to delete a record.
     * @param array $a_post
     * @return string
     */
    public function renderVerifyDelete(array $a_post = [])
    {
        $a_twig_values = $this->createDefaultTwigValues('library');
        $a_values = array(
            'what'         => 'Navigation ',
            'name'         => $a_twig_values['page_title'],
            'where'        => 'navigation',
            'btn_value'    => 'Navigation',
            'hidden_name'  => 'nav_id',
            'hidden_value' => $a_post['nav_id'],

        );
        $a_twig_values = array_merge($a_twig_values, $a_values);
        $tpl = '@' . LIB_TWIG_PREFIX . 'page/verify_delete.twig';
        return $this->o_twig->render($tpl, $a_twig_values);
    }

    /**
     * Creates an array for the twig values to display a select listing the urls.
     * @param int $url_id
     * @return array
     */
    private function createUrlSelect($url_id = -1)
    {
        $a_select = [
            'label_for'   => '',
            'label_class' => '',
            'label_text'  => '',
            'name'        => 'url_id',
            'class'       => 'form-control',
            'other_stuph' => '',
            'options'     => []
        ];
        $o_urls = new UrlsModel($this->o_db);
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

    /**
     * Creates an array for the twig values to display a select listing the navigation records.
     * @param int $nav_id
     * @return array
     */
    private function createNavSelect($nav_id = -1)
    {
        $meth = __METHOD__ . '.';
        $a_nav = $this->o_nav_complex->getNavListAll();
        $log_message = 'nav array: ' . var_export($a_nav, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        $a_select = [
            'label_for'   => '',
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
        if  ($nav_id == -1) {
            $a_options[] = [
                'value'       => -1,
                'label'       => 'SELF',
                'other_stuph' => ''
            ];
        }
        foreach ($a_nav as $nav) {
            $a_temp = [
                'value'       => $nav['nav_id'],
                'label'       => $nav['text'] . ' (level ' . $nav['level'] . ')',
                'other_stuph' => ''
            ];
            if ($nav['nav_id'] == $nav_id) {
                $a_temp['other_stuph'] = ' selected';
            }
            $a_options[] = $a_temp;
        }
        $a_select['options'] = $a_options;
        return $a_select;
    }

    /**
     * Creates an array for the twig values to display a select for the navigation group.
     * @param int $parent_ng_id The parent navigation group id
     * @return array
     */
    private function createNgSelect($parent_ng_id = -1)
    {
        $meth = __METHOD__ . '.';
        $o_ng = new NavgroupsModel($this->o_db);
        $results = $o_ng->read();
        $log_message = 'navgroups ' . var_export($results, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        $a_select = [
            'label_for'   => '',
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
}
