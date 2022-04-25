<?php /** @noinspection DuplicatedCode */
/** @noinspection NestedTernaryOperatorInspection */

/**
 * Trait ConfigViewTraits
 * @package Ritc_Library
 */
namespace Ritc\Library\Traits;

use Ritc\Library\Helper\ViewHelper;

/**
 * Common functions for the manager views.
 *
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   v3.1.4
 * @date      2018-06-25 13:12:22
 * @change_log
 * - v3.1.4 - bug fix                                                               - 2018-06-25 wer
 * - v3.1.0 - Forked this so ManagerViewTraits becomes primary                      - 2017-07-04 wer
 * - v3.0.0 - Renamed trait                                                         - 2017-06-20 wer
 * - v2.1.0 - changed method to use the twig value for tpl                          - 2017-05-10 wer
 * - v2.0.0 - changed to use ViewTraits and keep only manager like stuff in here    - 2017-02-07 wer
 * - v1.1.0 - manager links can be in two places.                                   - 12/15/2015 wer
 * - v1.0.1 - changed property name                                                 - 10/16/2015 wer
 * - v1.0.0 - think it is working now                                               - 10/05/2015 wer
 * - v0.1.0 - initial version                                                       - 10/01/2015 wer
 */
trait ConfigViewTraits
{
    use ManagerViewTraits;

    /**
     * Renders login form after resetting session.
     *
     * @param array $a_values optional default values ['tpl' => 'login', 'location' => '/manager/config/', 'a_message' => [], 'login_id' => '']
     * @return string
     */
    public function renderLogin(array $a_values = []):string
    {
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
        $a_twig_values['login_url'] = $location;
        $tpl = $this->createTplString($a_twig_values);
        return $this->renderIt($tpl, $a_twig_values);
    }

    /**
     * Renders the verify delete form.
     *
     * @param array $a_values required
     * @param array $a_options optional
     * @example "src/apps/Ritc/Library/resources/docs/examples/verify_delete.php" what the two parameters can have.
     * @return string
     */
    public function renderVerifyDelete(array $a_values = [], array $a_options = []):string
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
            ? empty($a_values['form_action'])
                ? '/manager/config/'
                : $a_values['form_action']
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
