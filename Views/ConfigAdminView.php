<?php
/**
 *  @brief View for the Configuration page.
 *  @file ConfigAdminView.php
 *  @ingroup blog views
 *  @namespace Ritc/Library/Views
 *  @class ConfigAdminView
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.1ß
 *  @date 2014-09-24 12:48:16
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.1ß - changed to use Base class and inject database object - 09/24/2014 wer
 *      v1.0.0ß - Initial version 04/02/2014 wer
 *  </pre>
**/
namespace Ritc\Library\Views;

use Ritc\Library\Abstracts\Base;
use Ritc\Library\Models\ConfigModel;
use Ritc\Library\Helper\ViewHelper;
use Zend\ServiceManager\ServiceManager;

class ConfigAdminView extends Base
{
    private $o_di;
    private $o_model;
    private $o_tpl;

    public function __construct(ServiceManager $o_di)
    {
        $this->setPrivateProperties();
        $this->o_di    = $o_di;
        $this->o_tpl   = $o_di->get('tpl');
        $this->o_model = new ConfigModel($o_di->get('db'));
    }
    /**
     *  Returns the list of configs in html.
     *  @param array $a_message
     *  @return string
     */
    public function renderConfigs(array $a_message = array())
    {
        $a_values = array(
            'public_dir' => '',
            'description' => 'Admin page for the app configuration.',
            'a_message' => array(),
            'a_configs' => array(
                array(
                    'config_id'    => '',
                    'config_name'  => '',
                    'config_value' => ''
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
                    'message'       => 'Changing configuration values can result in unexpected results. If you are not sure, do not do it.',
                    'type'          => 'warning'
                )
            );
        }
        $a_configs = $this->o_model->read();
        $this->logIt(
            'a_configs: ' . var_export($a_configs, TRUE),
            LOG_OFF,
            __METHOD__ . '.' . __LINE__
        );
        if ($a_configs !== false && count($a_configs) > 0) {
            $a_values['a_configs'] = $a_configs;
        }
        return $this->o_tpl->render('@pages/config_admin.twig', $a_values);
    }
    /**
     *  Returns HTML verify form to delete.
     *  @param array $a_values
     *  @return string
     */
    public function renderVerify(array $a_values = array())
    {
        if ($a_values === array()) {
            return $this->renderConfigs(array('message' => 'An Error Has Occurred. Please Try Again.', 'type' => 'failure'));
        }
        if (!isset($a_values['public_dir'])) {
            $a_values['public_dir'] = '';
        }
        if (!isset($a_values['description'])) {
            $a_values['description'] = 'Form to verify the action to delete the configuration.';
        }
        return $this->o_tpl->render('@pages/verify_delete_config.twig', $a_values);
    }
}
