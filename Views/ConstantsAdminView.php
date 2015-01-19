<?php
/**
 *  @brief View for the Configuration page.
 *  @file ConstantsAdminView.php
 *  @ingroup blog views
 *  @namespace Ritc/Library/Views
 *  @class ConstantsAdminView
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0β3
 *  @date 2014-11-17 14:02:35
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.0β3 - changed to use the new Di class                      - 11/17/2014 wer
 *      v1.0.0β2 - changed to use Base class and inject database object - 09/24/2014 wer
 *      v1.0.0β1 - Initial version                                      - 04/02/2014 wer
 *  </pre>
**/
namespace Ritc\Library\Views;

use Ritc\Library\Abstracts\Base;
use Ritc\Library\Models\ConstantsModel;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Services\Di;

class ConstantsAdminView extends Base
{
    private $o_model;
    private $o_tpl;

    public function __construct(Di $o_di)
    {
        $this->setPrivateProperties();
        $this->o_tpl   = $o_di->get('tpl');
        $this->o_model = new ConstantsModel($o_di->get('db'));
        if (DEVELOPER_MODE) {
            $this->o_elog = $o_di->get('elog');
        }
    }
    /**
     *  Returns the list of configs in html.
     *  @param array $a_message
     *  @return string
     */
    public function renderList(array $a_message = array())
    {
        $a_values = array(
            'public_dir'  => PUBLIC_DIR,
            'description' => 'Admin page for the app constants.',
            'a_message'   => array(),
            'a_constants' => array(
                array(
                    'const_id'    => '',
                    'const_name'  => '',
                    'const_value' => ''
                )
            ),
            'tolken'  => $_SESSION['token'],
            'form_ts' => $_SESSION['idle_timestamp'],
            'hobbit'  => ''
        );
        if (count($a_message) != 0) {
            $a_values['a_message'] = ViewHelper::messageProperties($a_message);
        }
        else {
            $a_values['a_message'] = ViewHelper::messageProperties(
                array(
                    'message' => 'Changing configuration values can result in unexpected results. If you are not sure, do not do it.',
                    'type'    => 'warning'
                )
            );
        }
        $a_constants = $this->o_model->read();
        $this->logIt(
            'constants: ' . var_export($a_constants, TRUE),
            LOG_ON,
            __METHOD__ . '.' . __LINE__
        );
        if ($a_constants !== false && count($a_constants) > 0) {
            $a_values['a_constants'] = $a_constants;
        }
        return $this->o_tpl->render('@pages/constants_admin.twig', $a_values);
    }
    /**
     *  Returns HTML verify form to delete.
     *  @param array $a_values
     *  @return string
     */
    public function renderVerify(array $a_values = array())
    {
        if ($a_values === array()) {
            return $this->renderList(array('message' => 'An Error Has Occurred. Please Try Again.', 'type' => 'failure'));
        }
        if (!isset($a_values['public_dir'])) {
            $a_values['public_dir'] = '';
        }
        if (!isset($a_values['description'])) {
            $a_values['description'] = 'Form to verify the action to delete the configuration.';
        }
        return $this->o_tpl->render('@pages/verify_delete_constant.twig', $a_values);
    }
}
