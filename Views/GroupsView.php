<?php
/**
 * Class GroupsView
 * @package Ritc_Library
 */
namespace Ritc\Library\Views;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Models\GroupsModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigViewTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * View for the Groups Admin page.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v2.1.0
 * @date    2017-06-20 11:44:34
 * @change_log
 * - v2.1.0   - Name changes elsewhere updated here.                                    - 2017-06-20 wer
 * - v2.0.0   - Name refactoring                                                        - 2017-05-14 wer
 * - v1.3.0   - Refactored the tpls to implement LIB_TWIG_PREFIX pushed changes here    - 2016-04-11 wer
 * - v1.2.0   - Bug fix to implementation of LIB_TWIG_PREFIX                            - 2016-04-10 wer
 * - v1.1.0   - Implement LIB_TWIG_PREFIX                                               - 12/12/2015 wer
 * - v1.0.0   - First working version                                                   - 11/27/2015 wer
 * - v1.0.0β1 - Initial version                                                         - 01/28/2015 wer
 * @todo rewrite the verify
 */
class GroupsView
{
    use LogitTraits, ConfigViewTraits;

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
        $this->o_groups->setElog($this->o_elog);
    }

    /**
     * Returns the list of routes in html.
     * @param array $a_message
     * @return string
     */
    public function renderList(array $a_message = [])
    {
        $meth = __METHOD__ . '.';
        try {
            $a_groups = $this->o_groups->read(array(), ['order_by' => 'group_name']);
        }
        catch (ModelException $e) {
            $a_groups = [];
        }
        if (!empty($a_groups)) {
            foreach ($a_groups as $a_group_key => $a_row) {
                $a_groups[$a_group_key]['group_description'] = html_entity_decode($a_row['group_description'], ENT_QUOTES);
                $selected_key_name = 'selected' . $a_row['group_auth_level'];
                $a_groups[$a_group_key][$selected_key_name] = ' selected';
            }
        }
        $a_twig_values = $this->createDefaultTwigValues($a_message);
        $a_twig_values['a_groups'] = $a_groups;
        $log_message = 'Twig values ' . var_export($a_twig_values, true);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        $tpl = $this->createTplString($a_twig_values);
        return $this->renderIt($tpl, $a_twig_values);
    }

    /**
     * Returns HTML verify form to delete.
     * @param array $a_values
     * @return string
     */
    public function renderVerify(array $a_values = [])
    {
        if ($a_values === array()) {
            $a_message = ViewHelper::failureMessage('An Error Has Occurred. Please Try Again.');
            return $this->renderList($a_message);
        }
        $a_values = [
            'what'         => 'Group',
            'name'         => $a_values['group_name'],
            'form_action'  => '/manager/config/groups/',
            'btn_value'    => $a_values['group_name'],
            'hidden_name'  => 'group_id',
            'hidden_value' => $a_values['group_id']
        ];
        $a_options = [
            'tpl'         => 'verify_delete',
            'page_prefix' => 'site_',
            'location'    => '/manager/config/groups/',
            'a_message'   => [],
            'fallback'    => 'renderList'
        ];
        return $this->renderVerifyDelete($a_values, $a_options);
    }
}
