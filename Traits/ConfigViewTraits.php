<?php
/**
 * @brief       Common functions for the manager views.
 * @details     Extends the ViewTraits to include functions used only in manager like situations.
 * @ingroup     lib_traits
 * @file        ConfigViewTraits.php
 * @namespace   Ritc\Library\Traits
 * @author      William E Reveal <bill@revealitconsulting.com>
 * @version     3.0.0
 * @date        2017-06-20 12:33:10
 * @note <b>Change Log</b>
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
    use ViewTraits;

    /**
     * Renders login form after resetting session.
     * @param string $login_id
     * @param array  $a_message Optional e.g. ['message' => '', 'type' => 'info']
     * @return string
     */
    public function renderLogin($login_id = '', array $a_message = [])
    {
        $this->o_session->resetSession();
        $a_twig_values = $this->createDefaultTwigValues($a_message, '/manager/config/');
        $a_twig_values['tpl'] = 'login';
        $tpl = $this->createTplString($a_twig_values);
        return $this->o_twig->render($tpl, $a_twig_values);
    }

    /**
     * @param array  $a_values        required \see #verifydelete
     * @param string $fallback_method optional
     * @return string
     */
    public function renderVerifyDelete(array $a_values = [], $fallback_method = 'renderList')
    {
        if (empty($a_values)) {
            $a_message = ViewHelper::errorMessage('Values required were missing.');
            return $this->$fallback_method($a_message);
        }
        $a_twig_values = $this->createDefaultTwigValues();
        $a_twig_values['tpl'] = 'verify_delete';

        $a_twig_values = array_merge($a_twig_values, $a_values);
        $tpl = $this->createTplString($a_twig_values);
        return $this->o_twig->render($tpl, $a_twig_values);
    }

}
