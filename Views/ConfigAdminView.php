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

use Ritc\Library\Models\ConfigAdminModel;
use Ritc\Library\Core\Base;
use Ritc\Library\Core\DbModel;
use Ritc\Library\Core\Tpl;
use Ritc\Library\Helper\ViewHelper;

class ConfigAdminView extends Base
{
    private $o_config;
    private $o_twig;
    protected $o_elog;

    public function __construct(DbModel $o_db)
    {
        $o_tpl          = new Tpl('twig_config.php');
        $this->o_twig   = $o_tpl->getTwig();
        $this->o_config = new ConfigAdminModel($o_db);
    }
    /**
     *  Returns the list of configs in html.
     *  @param array $a_message
     *  @return string
     */
    public function renderConfigs(array $a_message = array())
    {
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
        $a_configs = $this->o_config->read();
        $this->logIt('a_configs: ' . var_export($a_configs, TRUE), LOG_OFF, __METHOD__ . '.' . __LINE__);
        if ($a_configs !== false && count($a_configs) > 0) {
            $a_values['a_configs'] = $a_configs;
        }
        return $this->o_twig->render('@pages/app_config.twig', $a_values);
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
        return $this->o_twig->render('@pages/verify_delete_config.twig', $a_values);
    }
}
