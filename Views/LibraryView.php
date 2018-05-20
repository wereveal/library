<?php
namespace Ritc\Library\Views;

use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigViewTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * View for the Manager page.
 * @package RITC_Library
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v2.0.1
 * @date    2016-04-10 14:49:43
 * ## Change Log
 * - v2.0.2   - Refactored the tpls to implement LIB_TWIG_PREFIX pushed changes here    - 2016-04-11 wer
 * - v2.0.1   - Bug fix with implementation of LIB_TWIG_PREFIX                          - 2016-04-10 wer
 * - v2.0.0   - Refactored - name change.                                               - 2016-03-31 wer
 * - v1.1.1   - Implent LIB_TWIG_PREFIX                                                 - 12/12/2015 wer
 * - v1.1.0   - removed abstract class Base, use LogitTraits                            - 09/01/2015 wer
 * - v1.0.0   - First stable version                                                    - 01/16/2015 wer
 * - v1.0.0β2 - changed to match DI/IOC                                                 - 11/15/2014 wer
 * - v1.0.0β1 - Initial version                                                         - 11/08/2014 wer
 */
class LibraryView
{
    use LogitTraits, ConfigViewTraits;

    /**
     * LibraryView constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupElog($o_di);
        $this->setupView($o_di);
    }

    /**
     * Creates the home page of the Manager.
     * @param array $a_message A message, optional.
     * @return string
     */
    public function renderLandingPage(array $a_message = [])
    {
        $meth = __METHOD__ . '.';
        $this->setAdmLevel($_SESSION['login_id']);
        $a_values = $this->createDefaultTwigValues($a_message, '/manager/config/');
        $a_nav = $this->retrieveNav('ConfigLinks');
        $a_values['links'] = $a_nav;
        $tpl = $this->createTplString($a_values);
        $log_message = 'twig values ' . var_export($a_values, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
        $this->logIt("Template: " . $tpl, LOG_OFF, $meth . __LINE__);
        return $this->renderIt($tpl, $a_values);
    }

    /**
     * Temp method to test stuff
     * @param array $a_args
     * @return mixed
     * @todo rewrite for ViewTraits
     */
    public function renderTempPage(array $a_args = [])
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
            'title'       => 'This is a Temp Page',
            'a_message'   => $a_message,
            'body_text'   => $body_text,
            'page_prefix' => LIB_TWIG_PREFIX
        ];
        $a_values = array_merge($a_page_values, $a_values);
        $tpl = '@' . LIB_TWIG_PREFIX . 'pages/list_logins.twig';
        return $this->renderIt($tpl, $a_values);
    }
}
