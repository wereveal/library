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

    private $o_db;

    public function __construct(Di $o_di)
    {
        $this->o_db = $o_di->get('db');
        $this->setupView($o_di);
    }
    public function renderLandingPage()
    {
        $a_values = [
            'description'   => 'This is the Manager Page',
            'public_dir'    => '',
            'title'         => 'This is the Main Manager Page',
            'links'         => $this->a_links,
            'site_url'      => SITE_URL,
            'rights_holder' => RIGHTS_HOLDER,
            'menus'         => $this->a_links
        ];
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
        $a_values = [
            'description'   => 'This is the Tester Page',
            'public_dir'    => '',
            'title'         => 'This is the Main Tester Page',
            'a_message'     => $a_message,
            'body_text'     => $body_text,
            'site_url'      => SITE_URL,
            'rights_holder' => RIGHTS_HOLDER
        ];
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
        $a_values = [
            'tolken'    => $tolken,
            'form_ts'   => $idle_ts,
            'hobbit'    => '',
            'login_id'  => $previous_login_id,
            'password'  => '',
            'a_message' => $a_message,
            'menus'     => $this->a_links
        ];
        $o_sess->unsetVar('login_id');
        return $this->o_twig->render('@pages/login_form.twig', $a_values);
    }
}
