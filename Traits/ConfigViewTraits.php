<?php
/**
 * @brief       Common functions for the manager views.
 * @details     Extends the ViewTraits to include functions used only in manager like situations.
 * @ingroup     lib_traits
 * @file        ConfigViewTraits.php
 * @namespace   Ritc\Library\Traits
 * @author      William E Reveal <bill@revealitconsulting.com>
 * @version     3.1.3
 * @date        2018-04-14 15:23:19
 * @note <b>Change Log</b>
 * - v3.1.3 - bug fix (some variables seemed to be confused)                        - 2018-04-14 wer
 * - v3.1.2 - bug fix                                                               - 2017-12-13 wer
 * - v3.1.1 - bug fix for TwigExceptions now handled by new method in ViewTraits    - 2017-12-02 wer
 * - v3.1.0 - Forked this so ManagerViewTraits becomes primary                      - 2017-07-04 wer
 * - v3.0.0 - Renamed trait                                                         - 2017-06-20 wer
 * - v2.1.0 - changed method to use the twig value for tpl                          - 2017-05-10 wer
 * - v2.0.1 - minor bug fixes                                                       - 2017-02-11 wer
 * - v2.0.0 - changed to use ViewTraits and keep only manager like stuff in here    - 2017-02-07 wer
 * - v1.1.0 - manager links can be in two places.                                   - 12/15/2015 wer
 * - v1.0.2 - bug fix                                                               - 11/24/2015 wer
 * - v1.0.1 - changed property name                                                 - 10/16/2015 wer
 * - v1.0.0 - think it is working now                                               - 10/05/2015 wer
 * - v0.1.0 - initial version                                                       - 10/01/2015 wer
 */
namespace Ritc\Library\Traits;

use Ritc\Library\Helper\ViewHelper;

/**
 * Class ConfigViewTraits
 * @class   ConfigViewTraits
 * @package Ritc\Library\Traits
 */
trait ConfigViewTraits
{
    use ManagerViewTraits;

    /**
     * Renders login form after resetting session.
     * @param array $a_values optional default values ['tpl' => 'login', 'location' => '/manager/config/', 'a_message' => [], 'login_id' => '']
     * @return string
     */
    public function renderLogin(array $a_values = [])
    {
        $meth = __METHOD__ . '.';
        $this->o_session->resetSession();
        $location = empty($a_values['location'])
            ? '/manager/config/'
            : $a_values['location'];
        $a_message = empty($a_values['a_message'])
            ? []
            : $a_values['a_message'];
        $a_twig_values = $this->createDefaultTwigValues($a_message, $location);
        $a_twig_values['login_id'] = empty($a_values['login_id'])
            ? ''
            : $a_values['login_id'];
        $a_twig_values['tpl'] = empty($a_values['tpl'])
            ? 'login'
            : $a_values['tpl'];
        $tpl = $this->createTplString($a_twig_values);
          $log_message = 'twig values:  ' . var_export($a_twig_values, TRUE);
          $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
          $log_message = 'Session:  ' . var_export($_SESSION, TRUE);
          $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
        return $this->renderIt($tpl, $a_twig_values);
    }

    /**
     * @param array $a_values required \see #verifydelete
     * @param array $a_options optional \see #verifydelete
     * @return string
     */
    public function renderVerifyDelete(array $a_values = [], array $a_options = [])
    {
        if (empty($a_values)) {
            $fallback_method = empty($a_options['fallback'])
                ? 'renderList'
                : $a_options['fallback']
            ;
            $a_message = ViewHelper::errorMessage('Values required were missing.');
            return $this->$fallback_method($a_message);
        }
        $a_message = empty($a_options['a_message'])
            ? []
            : $a_options['a_message'];
        $location = empty($a_options['location'])
            ? '/manager/config/'
            : $a_options['location'];
        $a_twig_values = $this->createDefaultTwigValues($a_message, $location);
        $a_twig_values['location'] = empty($a_options['location'])
            ? empty($a_twig_values['where']) ? '' : $a_twig_values['where']
            : $a_options['location'];
        $a_twig_values['tpl'] = empty($a_options['tpl'])
            ? 'verify_delete'
            : $a_options['tpl'];

        $a_twig_values = array_merge($a_twig_values, $a_values);
        $tpl = $this->createTplString($a_twig_values);
        return $this->renderIt($tpl, $a_twig_values);
    }
}
