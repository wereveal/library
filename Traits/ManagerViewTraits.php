<?php
/**
 * @brief       Common functions for the manager views.
 * @details     Extends the ViewTraits to include functions used only in manager like situations.
 * @ingroup     lib_traits
 * @file        ManagerViewTraits.php
 * @namespace   Ritc\Library\Traits
 * @author      William E Reveal <bill@revealitconsulting.com>
 * @version     3.1.0
 * @date        2017-07-04 13:35:33
 * @note <b>Change Log</b>
 * - v3.1.0 - Reverted name back to ManagerViewTraits                               - 2017-07-04 wer
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
 * Class ManagerViewTraits
 * @class   ManagerViewTraits
 * @package Ritc\Library\Traits
 */
trait ManagerViewTraits
{
    use ViewTraits;

    /**
     * Renders login form after resetting session.
     * @param array $a_values optional default values ['tpl' => 'login', 'location' => '/manager/', 'a_message' => [], 'login_id' => '']
     * @return string
     */
    public function renderLogin(array $a_values = [])
    {
        $this->o_session->resetSession();
        $location = empty($a_values['location'])
            ? '/manager/'
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
        return $this->o_twig->render($tpl, $a_twig_values);
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
                ? 'render'
                : $a_options['fallback']
            ;
            $a_message = ViewHelper::errorMessage('Values required were missing.');
            return $this->$fallback_method($a_message);
        }
        $a_message = empty($a_options['a_message'])
            ? []
            : $a_options['a_message'];
        $location = empty($a_options['location'])
            ? '/manager/'
            : $a_options['location'];
        $a_twig_values = $this->createDefaultTwigValues($a_message, $location);
        $a_twig_values['tpl'] = empty($a_options['tpl'])
            ? 'verify_delete'
            : $a_options['tpl'];

        $a_twig_values = array_merge($a_twig_values, $a_values);
        $tpl = $this->createTplString($a_twig_values);
        return $this->o_twig->render($tpl, $a_twig_values);
    }
}
