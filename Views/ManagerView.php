<?php
/**
 *  @brief View for the Manager page.
 *  @file ManagerView.php
 *  @ingroup Library views
 *  @namespace Ritc/Library/Views
 *  @class ManagerView
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.1β
 *  @date 2014-11-15 15:00:40
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.1β - changed to match DI/IOC - 11/15/2014 wer
 *      v1.0.0β - Initial version         - 11/08/2014 wer
 *  </pre>
 **/
namespace Ritc\Library\Views;

use Ritc\Library\Abstracts\Base;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Services\Di;

class ManagerView extends Base
{
    private $o_di;
    private $o_db;
    private $o_tpl;

    public function __construct(Di $o_di)
    {
        $this->setPrivateProperties();
        $this->o_di  = $o_di;
        $this->o_tpl = $o_di->get('tpl');
        $this->o_db  = $o_di->get('db');
    }
    public function renderLandingPage()
    {
        $a_links = [
            [
                'text' => 'Home',
                'url'  => '/manager/',
            ],
            [
                'text' => 'Configuration Manger',
                'url'  => '/manager/configs/',
            ],
            [
                'text' => 'Router Manager',
                'url'  => '/manager/routes/'
            ],
            [
                'text' => 'People Manager',
                'url'  => '/manager/people/',
            ],
            [
                'text' => 'Groups Manager',
                'url'  => '/manager/groups/',
            ],
            [
                'text' => 'Roles Manager',
                'url'  => '/manager/roles/',
            ]
        ];
        $a_values = [
            'description'   => 'This is the Manager Page',
            'public_dir'    => '',
            'title'         => 'This is the Main Manager Page',
            'links'         => $a_links,
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
        $a_message = array();
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
            'a_message'     => $a_message,
            'body_text'     => $body_text,
            'site_url'      => SITE_URL,
            'rights_holder' => RIGHTS_HOLDER
        ];
        return $this->o_tpl->render('@main/list_logins.twig', $a_values);
    }
    /**
     * Creates the html that displays the login form to access the app.
     * Sometimes this will have been handled already elsewhere.
     * @param string $previous_login_id optional, allows the user_login_id to be used over.
     * @return string
     */
    public function renderLoginForm($previous_login_id = '', $message = '')
    {
        if ($message != '') {
            $a_message = ViewHelper::messageProperties(['message' => $message, 'type' => 'failure']);
        }
        else {
            $a_message = array();
        }
        $a_values = [
            'tolken'    => $_SESSION['token'],
            'form_ts'   => $_SESSION['idle_timestamp'],
            'hobbit'    => '',
            'login_id'  => $previous_login_id,
            'password'  => '',
            'a_message' => $a_message
        ];
        return $this->o_tpl->render('@pages/login_form.twig', $a_values);
    }
}
