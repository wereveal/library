<?php
/**
 * @brief     View for the Groups Admin page.
 * @ingroup   lib_views
 * @file      GroupsView.phpnamespace Ritc\Library\Views
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   2.0.0
 * @date      2017-05-14 16:42:34
 * @note <b>Change Log</b>
 * - v2.0.0   - Name refactoring                                                        - 2017-05-14 wer
 * - v1.0.3   - Refactored the tpls to implement LIB_TWIG_PREFIX pushed changes here    - 2016-04-11 wer
 * - v1.0.2   - Bug fix to implementation of LIB_TWIG_PREFIX                            - 2016-04-10 wer
 * - v1.0.1   - Implent LIB_TWIG_PREFIX                                                 - 12/12/2015 wer
 * - v1.0.0   - First working version                                                   - 11/27/2015 wer
 * - v1.0.0β1 - Initial version                                                         - 01/28/2015 wer
 */
namespace Ritc\Library\Views;

use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Models\GroupsModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Traits\ViewTraits;

/**
 * Class GroupsView
 * @class   GroupsView
 * @package Ritc\Library\Views
 */
class GroupsView
{
    use LogitTraits, ViewTraits;

    /**
     * @var \Ritc\Library\Models\GroupsModel
     */
    private $o_groups;

    /**
     * GroupsView constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupElog($o_di);
        $this->setupView($o_di);
        $this->o_groups = new GroupsModel($this->o_db);
        if (DEVELOPER_MODE && is_object($this->o_elog)) {
            $this->o_groups->setElog($this->o_elog);
        }
    }

    /**
     * Returns the list of routes in html.
     * @param array $a_message
     * @return string
     */
    public function renderList(array $a_message = array())
    {
        $meth    = __METHOD__ . '.';
        $a_values = [
            'a_message'   => array(),
            'a_groups'    => array(
                [
                    'group_id'          => '',
                    'group_name'        => '',
                    'group_description' => '',
                    'group_immutable'   => 0
                ]
            ),
            'tolken'      => $_SESSION['token'],
            'form_ts'     => $_SESSION['idle_timestamp'],
            'hobbit'      => '',
            'a_menus'     => $this->a_nav,
            'adm_lvl'     => $this->adm_level,
            'twig_prefix' => LIB_TWIG_PREFIX
        ];
        $a_page_values = $this->getPageValues();
        $a_values = array_merge($a_page_values, $a_values);
        if (count($a_message) != 0) {
            $a_values['a_message'] = ViewHelper::messageProperties($a_message);
        }
        else {
            $a_values['a_message'] = '';
        }
        $a_groups = $this->o_groups->read(array(), ['order_by' => 'group_name']);
        if ($a_groups !== false && count($a_groups) > 0) {
            $this->logIt("Groups: " . var_export($a_groups, true), LOG_OFF, $meth . __LINE__);
            foreach ($a_groups as $a_group_key => $a_row) {
                $a_groups[$a_group_key]['group_description'] = html_entity_decode($a_row['group_description'], ENT_QUOTES);
                $selected_key_name = 'selected' . $a_row['group_auth_level'];
                $a_groups[$a_group_key][$selected_key_name] = ' selected';
            }
            $a_values['a_groups'] = $a_groups;
        }
        $log_message = 'a_values: ' . var_export($a_values, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
        $tpl = '@' . LIB_TWIG_PREFIX . 'pages/groups.twig';
        return $this->o_twig->render($tpl, $a_values);
    }

    /**
     * Returns HTML verify form to delete.
     * @param array $a_values
     * @return string
     */
    public function renderVerify(array $a_values = array())
    {
        if ($a_values === array()) {
            return $this->renderList(['message' => 'An Error Has Occurred. Please Try Again.', 'type' => 'failure']);
        }
        if (!isset($a_values['public_dir'])) {
            $a_values['public_dir'] = '';
        }
        if (!isset($a_values['description'])) {
            $a_values['description'] = 'Form to verify the action to delete the group.';
        }
        $a_values['menus'] = $this->a_nav;
        $a_values['twig_prefix'] = LIB_TWIG_PREFIX;
        $tpl = '@' . LIB_TWIG_PREFIX . 'pages/verify_delete_group.twig';
        return $this->o_twig->render($tpl, $a_values);
    }
}
