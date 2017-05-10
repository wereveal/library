<?php
/**
 * @brief     Commonly used functions used in Manager Controllers.
 * @details   Commonly used functions used in Manager Controllers. Expands on Controller Traits.
 * @ingroup   lib_traits
 * @file      Ritc/Library/Traits/ManagerControllerTraits.php
 * @namespace Ritc\Library\Traits
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.0
 * @date      2017-05-10 10:55:02
 * @note Change Log
 * - v1.0.0-alpha.0 - Initial version                                                                   - 2017-05-10 wer
 */
namespace Ritc\Library\Traits;

use Ritc\Library\Helper\AuthHelper;
use Ritc\Library\Helper\ViewHelper;

/**
 * Class ManagerControllerTraits.
 * @class   ManagerControllerTraits
 * @package Ritc\Library\Traits
 */
trait ManagerControllerTraits
{
    use ControllerTraits;

    /**
     * @var AuthHelper
     */
    protected $o_auth;

    /**
     * Verifies that the person is logged in and is valid
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
     * @return array The standard basic message array with message and message type.
     */
    protected function verifyLogin()
    {
        $a_results = $this->o_auth->login($this->a_post); // authentication part
        if ($a_results['is_logged_in'] == 1) {
            $this->o_session->setVar('login_id', $a_results['login_id']);
            $min_auth_level = $this->a_router_parts['min_auth_level'];
            if ($this->o_auth->isAllowedAccess($a_results['people_id'], $min_auth_level)) { // authorization part
                return ViewHelper::successMessage('Success, you are now logged in!');
            }
        }
        /* well, apparently they weren't allowed access so kick em to the curb */
        if ($a_results['is_logged_in'] == 1) {
            $this->o_auth->logout($a_results['people_id']);
        }
        return isset($a_results['message'])
            ? ViewHelper::failureMessage($a_results['message'])
            : ViewHelper::failureMessage('Login Id or Password was incorrect. Please Try Again');
    }
}