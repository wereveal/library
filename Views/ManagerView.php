<?php
/**
 *  @brief View for the Manager page.
 *  @file ManagerView.php
 *  @ingroup ritc_library views
 *  @namespace Ritc/Library/Views
 *  @class ManagerView
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.1.0
 *  @date 2015-09-01 08:05:20
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v1.1.0   - removed abstract class Base, use LogitTraits - 09/01/2015 wer
 *      v1.0.0   - First stable version                         - 01/16/2015 wer
 *      v1.0.0β2 - changed to match DI/IOC                      - 11/15/2014 wer
 *      v1.0.0β1 - Initial version                              - 11/08/2014 wer
 *  </pre>
 **/
namespace Ritc\Library\Views;

use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Traits\ManagerViewTraits;

class ManagerView
{
    use LogitTraits, ManagerViewTraits;

    public function __construct(Di $o_di)
    {
        $this->setupView($o_di);
        if (defined('DEVELOPER_MODE') && DEVELOPER_MODE) {
            $this->o_elog = $o_di->get('elog');
        }
    }
    public function renderLandingPage($a_message = array())
    {
        $meth = __METHOD__ . '.';
        $this->setAuthLevel($_SESSION['login_id']);
        $this->setLinks();
        $a_values = $this->getPageValues();
        $this->logIt('Links Again: ' . var_export($this->a_links, true), LOG_OFF, $meth . __LINE__);
        $a_values['links']   = $this->a_links;
        $a_values['menus']   = $this->a_links;
        if (is_array($a_message)) {
            $a_values['a_message'] = ViewHelper::messageProperties($a_message);
        }
        else {
            $a_values['a_message'] = ViewHelper::messageProperties(['message' => '']);
        }
        $this->logIt('Final Values for twig: ' . var_export($a_values, true), LOG_OFF, $meth . __LINE__);
        return $this->o_twig->render('@main/index.twig', $a_values);
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
        $a_page_values = $this->getPageValues();
        $a_values = [
            'title'         => 'This is a Temp Page',
            'a_message'     => $a_message,
            'body_text'     => $body_text
        ];
        $a_values = array_merge($a_page_values, $a_values);
        return $this->o_twig->render('@main/list_logins.twig', $a_values);
    }
    /**
     * Creates the html that displays the login form to access the app.
     * Sometimes this will have been handled already elsewhere.
     * @param string $previous_login_id optional, allows the user_login_id to be used over.
     * @param array $a_message array with message and type of message.
     * @return string
     */
    public function renderLoginForm($previous_login_id = '', array $a_message = array())
    {
        $o_sess  = $this->o_di->get('session');
        $tolken  = $o_sess->getVar('token');
        $idle_ts = $o_sess->getVar('idle_timestamp');
        if ($tolken == '' || $idle_ts == '') {
            $o_sess->resetSession();
            $tolken  = $o_sess->getVar('token');
            $idle_ts = $o_sess->getVar('idle_timestamp');
        }
        if ($a_message != array()) {
            $a_message = ViewHelper::messageProperties($a_message);
        }
        else {
            $a_message = array();
        }
        $a_page_values = $this->getPageValues();
        $a_values = [
            'tolken'    => $tolken,
            'form_ts'   => $idle_ts,
            'hobbit'    => '',
            'login_id'  => $previous_login_id,
            'password'  => '',
            'a_message' => $a_message,
            'menus'     => array()
        ];
        $a_values = array_merge($a_page_values, $a_values);
        $o_sess->unsetVar('login_id');
        return $this->o_twig->render('@pages/login_form.twig', $a_values);
    }
}
