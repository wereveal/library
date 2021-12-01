<?php
/**
 * Class LibraryView
 * @package Ritc_Library
 */
namespace Ritc\Library\Views;

use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigViewTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * View for the Config Manager page.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 2.2.0+
 * @date    2018-05-21 16:39:50
 * @change_log
 * - 2.2.0   - Removed unused method                                                   - 2018-05-21 wer
 * - 2.1.0   - Refactored the tpls to implement LIB_TWIG_PREFIX pushed changes here    - 2016-04-11 wer
 * - 2.0.0   - Refactored - name change.                                               - 2016-03-31 wer
 * - 1.2.0   - Implement LIB_TWIG_PREFIX                                               - 12/12/2015 wer
 * - 1.1.0   - removed abstract class Base, use LogitTraits                            - 09/01/2015 wer
 * - 1.0.0   - First stable version                                                    - 01/16/2015 wer
 * - 1.0.0β2 - changed to match DI/IOC                                                 - 11/15/2014 wer
 * - 1.0.0β1 - Initial version                                                         - 11/08/2014 wer
 */
class LibraryView
{
    use LogitTraits;
    use ConfigViewTraits;

    /**
     * LibraryView constructor.
     *
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupElog($o_di);
        $this->setupView($o_di);
    }

    /**
     * Creates the home page of the Manager.
     *
     * @param array $a_message A message, optional.
     * @return string
     */
    public function renderLandingPage(array $a_message = []):string
    {
        $meth = __METHOD__ . '.';
        $this->setAdmLevel($_SESSION['login_id']);
        $a_values = $this->createDefaultTwigValues($a_message);
        $a_nav = $this->retrieveNav('ConfigLinks');
        $a_values['links'] = $a_nav;
        $tpl = $this->createTplString($a_values);
          $log_message = 'twig values ' . var_export($a_values, TRUE);
          $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
          $this->logIt('Template: ' . $tpl, LOG_OFF, $meth . __LINE__);
        return $this->renderIt($tpl, $a_values);
    }
}
