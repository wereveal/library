<?php
/**
 *  @brief View for the Manager page.
 *  @file ManagerView.php
 *  @ingroup Library views
 *  @namespace Ritc/Library/Views
 *  @class ManagerView
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.1ß
 *  @date 2014-11-15 15:00:40
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.1ß - changed to match DI/IOC - 11/15/2014 wer
 *      v1.0.0ß - Initial version         - 11/08/2014 wer
 *  </pre>
 **/
namespace Ritc\Library\Views;

use Ritc\Library\Abstracts\Base;
use Ritc\Library\Helper\ViewHelper;
use Zend\ServiceManager\ServiceManager;

class ManagerView extends Base
{
    private $o_di;
    private $o_db;
    private $o_tpl;

    public function __construct(ServiceManager $o_di)
    {
        $this->setPrivateProperties();
        $this->o_di  = $o_di;
        $this->o_tpl = $o_di->get('tpl');
        $this->o_db  = $o_di->get('db');
    }
    public function renderLandingPage()
    {
        $message = ViewHelper::messageProperties(array());
        $a_values = [
            'description'   => 'This is the Tester Manager Page',
            'public_dir'    => '',
            'title'         => 'This is the Main Manager Test Page',
            'message'       => $message,
            'body_text'     => 'Hello',
            'site_url'      => SITE_URL,
            'rights_holder' => RIGHTS_HOLDER
        ];
        return $this->o_tpl->render('@main/index.twig', $a_values);
    }
    /**
     * Temp method to test stuff
     * @param array $a_args
     * @return mixed
     */
    public function renderTempPage(array $a_args)
    {
        $message = '';
        if (is_array($a_args)) {
            $body_text = "it of course was an array";
        }
        else {
            $body_text =  "something seriously is wrong.
                The array in in the definition should have prevented this from happening.";
        }
        $a_values = [
            'description'   => 'This is the Tester Page',
            'public_dir'    => '',
            'title'         => 'This is the Main Tester Page',
            'message'       => $message,
            'body_text'     => $body_text,
            'site_url'      => SITE_URL,
            'rights_holder' => RIGHTS_HOLDER
        ];
        return $this->o_tpl->render('@main/index.twig', $a_values);
    }
}
