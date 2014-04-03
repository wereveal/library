<?php
/**
 *  @brief View for the Configuration page.
 *  @file ConfigAdminView.php
 *  @ingroup blog views
 *  @namespace Ritc/Library/Views
 *  @class ConfigAdminView
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0
 *  @date 2014-04-02 13:04:04
 *  @note A file in Library v5
 *  @note <pre><b>Change Log</b>
 *      v1.0.0 - Initial version 04/02/2014 wer
 *  </pre>
**/
namespace Ritc\Library\Views;

use Ritc\Library\Models\ConfigAdminModel;
use Ritc\Library\Core\Elog;
use Ritc\Library\Core\Tpl;
use Ritc\Library\Helper\ViewHelper;

class ConfigAdminView
{
    private $o_config;
    private $o_elog;
    private $o_twig;
    private $o_vhelp;

    public function __construct()
    {
        $this->o_elog   = Elog::start();
        $o_tpl          = new Tpl('twig_config.php');
        $this->o_twig   = $o_tpl->getTwig();
        $this->o_vhelp  = new ViewHelper();
        $this->o_config = new ConfigAdminModel;
    }
    /**
     *  Returns the list of configs in html.
     *  @param array $a_message
     *  @return string
     **/
    public function renderConfigs(array $a_message = array()){
        $a_values = array(
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
        if ($a_message != array()) {
            $a_values['a_message'] = $this->o_vhelp->messageProperties($a_message);
        }
        else {
            $a_values['a_message'] = $this->o_vhelp->messageProperties(
                array(
                    'message'       => 'Changing configuration values can result in unexpected results. If you are not sure, do not do it.',
                    'type'          => 'warning'
                )
            );
        }
        $a_configs = $this->o_config->read();
        $this->o_elog->write('a_configs: ' . var_export($a_configs, TRUE), LOG_OFF, __METHOD__ . '.' . __LINE__);
        if ($a_configs !== false && count($a_configs) > 0) {
            $a_values['a_configs'] = $a_configs;
        }
        return $this->o_twig->render('@pages/app_config.twig', $a_values);
    }
    public function renderVerify(array $a_values = array())
    {
        if ($a_values === array()) {
            return $this->renderConfigs(array('message' => 'An Error Has Occurred. Please Try Again.', 'type' => 'failure'));
        }
        return $this->o_twig->render('@pages/verify_delete_config.twig', $a_values);
    }
}
