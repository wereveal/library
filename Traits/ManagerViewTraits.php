<?php
/**
 * @brief       Common functions for the manager views.
 * @details     Extends the ViewTraits to include functions used only in manager like situations.
 * @ingroup     lib_traits
 * @file        ManagerViewTraits.php
 * @namespace   Ritc\Library\Traits
 * @author      William E Reveal <bill@revealitconsulting.com>
 * @version     2.0.1
 * @date        2017-02-11 11:40:46
 * @note <b>Change Log</b>
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
     * @param string $login_id
     * @param array  $a_message Optional e.g. ['message' => '', 'type' => 'info']
     * @return string
     */
    public function renderLogin($login_id = '', array $a_message = [])
    {
        $meth = __METHOD__ . '.';
        $this->o_session->resetSession();
        if (!empty($a_message)) {
            $a_message = ViewHelper::messageProperties($a_message);
        }
        else {
            $a_message = [];
        }

        $a_twig_values = $this->createDefaultTwigValues($a_message);
        $a_twig_values['login_id'] = $login_id;
        $a_twig_values['a_menus'] = [];

        $log_message = 'Final Twig Values:  ' . var_export($a_twig_values, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
        $this->o_session->unsetVar('login_id');
        $tpl = '@' . $a_twig_values['twig_prefix'] . 'pages/login_page.twig';
        return $this->o_twig->render($tpl, $a_twig_values);
    }

}
