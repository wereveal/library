<?php
namespace Ritc\Library\Views;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Models\ConstantsModel;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigViewTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * View for the Configuration page.
 * @package RITC_Library
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v2.2.0
 * @date    2017-06-20 11:43:56
 * ## Change Log
 * - v2.2.0   - refactoring elsewhere made changes here.                                - 2017-06-20 wer
 * - v2.1.0   - Updated to match ViewTraits and fix misc bugs.                          - 2017-06-07 wer
 * - v2.0.0   - Name refactoring                                                        - 2017-05-14 wer
 * - v1.2.4   - Refactored the tpls to implement LIB_TWIG_PREFIX pushed changes here    - 2016-04-11 wer
 * - v1.2.2   - Implement LIB_TWIG_PREFIX                                               - 12/12/2015 wer
 * - v1.2.0   - Immutable code added                                                    - 10/07/2015 wer
 * - v1.1.0   - removed abstract class Base, added LogitTraits                          - 09/01/2015 wer
 * - v1.0.0   - first fully working version                                             - 01/28/2015 wer
 * - v1.0.0β3 - changed to use the new Di class                                         - 11/17/2014 wer
 * - v1.0.0β2 - changed to use Base class and inject database object                    - 09/24/2014 wer
 * - v1.0.0β1 - Initial version                                                         - 04/02/2014 wer
 */
class ConstantsView
{
    use LogitTraits, ConfigViewTraits;

    /** @var \Ritc\Library\Views\LibraryView */
    private $o_view;

    /**
     * ConstantsView constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupElog($o_di);
        $this->setupView($o_di);
        $this->o_view = new LibraryView($o_di);
    }

    /**
     * Returns the list of configs in html.
     * @param array $a_message
     * @return string
     */
    public function renderList(array $a_message = array())
    {
        /** @var \Ritc\Library\Services\DbModel $o_db */
        $o_db = $this->o_di->get('db');
        $o_const = new ConstantsModel($o_db);
        $o_const->setElog($this->o_elog);

        if (count($a_message) != 0) {
            $a_message['message'] .= "<br><br>Changing configuration values can result in unexpected results. If you are not sure, do not do it.";
        }
        else {
            $a_message = ViewHelper::warningMessage('Changing configuration values can result in unexpected results. If you are not sure, do not do it.');
        }
        $a_twig_values = $this->createDefaultTwigValues($a_message);
        try {
            $a_constants = $o_const->read([], ['order_by' => 'const_name']);
            if (!empty($a_constants)) {
                $a_twig_values['a_constants'] = $a_constants;
            }
            else {
                $a_twig_values['a_constants'] = [];
            }
        }
        catch (ModelException $e) {
            $a_twig_values['a_constants'] = [];
        }
        $tpl = $this->createTplString($a_twig_values);
        return $this->renderIt($tpl, $a_twig_values);
    }
}
