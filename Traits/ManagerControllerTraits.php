<?php
/**
 * Trait ManagerControllerTraits
 * @package Ritc_Library
 */
namespace Ritc\Library\Traits;

use Ritc\Library\Helper\AuthHelper;
use Ritc\Library\Helper\ViewHelper;

/**
 * Commonly used functions used in Manager Controllers.
 * Expands on Controller Traits.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-alpha.3
 * @date    2017-07-13 11:40:17
 * @change_log
 * - v1.0.0-alpha.3 - Removed SetOAuth and renamed getOAuth to getAuth.                   - 2017-07-13 wer
 * - v1.0.0-alpha.2 - Reverted back to ManagerControllerTraits                            - 2017-07-04 wer
 * - v1.0.0-alpha.1 - Renamed Trait                                                       - 2017-06-20 wer
 * - v1.0.0-alpha.0 - Initial version                                                     - 2017-05-10 wer
 */
trait ManagerControllerTraits
{
    use ControllerTraits;

    /** @var AuthHelper */
    protected $o_auth;

    /**
     * Verifies that the person is logged in and is valid
     *
     * @return bool
     */
    protected function loginValid()
    {
        if (isset($_SESSION['login_id']) && $_SESSION['login_id'] != '') {
            $min_auth_level = $this->a_router_parts['min_auth_level'];
            if ($this->o_auth->isAllowedAccess($_SESSION['login_id'], $min_auth_level)) {
                $this->o_session->updateIdleTimestamp();
                return true;
            }
        }
        return false;
    }

    /**
     * Creates the o_auth instance property for the class
     *
     * @param $o_di
     */
    protected function setupManagerController($o_di)
    {
        $this->setupController($o_di);
        $o_auth = new AuthHelper($o_di);
        $this->o_auth = $o_auth;
    }

    /**
     * Verifies the login attempt via the login form.
     *
     * @return array The standard basic message array with message and message type.
     */
    protected function verifyLogin()
    {
        $a_results = $this->o_auth->login($this->a_post); // authentication part
        if ($a_results['is_logged_in'] == 'true') {
            $min_auth_level = $this->a_router_parts['min_auth_level'];
            if ($this->o_auth->isAllowedAccess($a_results['people_id'], $min_auth_level, true)) { // authorization part
                $this->o_session->setVar('login_id', $a_results['login_id']);
                $this->o_session->setVar('adm_lvl', $a_results['auth_level']);
                return ViewHelper::successMessage('Success, you are now logged in!');
            }
            else {
                $this->o_auth->logout($a_results['people_id']);
                $a_results = ['message' => 'Sorry, you are not allowed access at this time.'];
            }
        }
        return isset($a_results['message'])
            ? ViewHelper::failureMessage($a_results['message'])
            : ViewHelper::failureMessage('Login Id or Password was incorrect. Please Try Again');
    }

    /**
     * Gets the o_auth property.
     *
     * @return \Ritc\Library\Helper\AuthHelper
     */
    public function getAuth()
    {
        return $this->o_auth;
    }
}
