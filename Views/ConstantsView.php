<?php
/**
 * @brief     View for the Configuration page.
 * @ingroup   lib_views
 * @file      ConstantsView.phpnamespace Ritc\Library\Views
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   2.1.0
 * @date      2017-06-07 09:54:17
 * @note <b>Change Log</b>
 * - v2.1.0   - Updated to match ViewTraits and fix misc bugs.                          - 2017-06-07 wer
 * - v2.0.0   - Name refactoring                                                        - 2017-05-14 wer
 * - v1.2.4   - Bug fix, removed unneeded use statement                                 - 2016-04-11 wer
 *              Refactored the tpls to implement LIB_TWIG_PREFIX pushed changes here
 * - v1.2.3   - Bug fix in the implementation of LIB_TWIG_PREFIX                        - 2016-04-10 wer
 * - v1.2.2   - Implement LIB_TWIG_PREFIX                                               - 12/12/2015 wer
 * - v1.2.1   - Bug Fix                                                                 - 11/07/2015 wer
 * - v1.2.0   - Immutable code added                                                    - 10/07/2015 wer
 * - v1.1.0   - removed abstract class Base, added LogitTraits                          - 09/01/2015 wer
 * - v1.0.0   - first fully working version                                             - 01/28/2015 wer
 * - v1.0.0β3 - changed to use the new Di class                                         - 11/17/2014 wer
 * - v1.0.0β2 - changed to use Base class and inject database object                    - 09/24/2014 wer
 * - v1.0.0β1 - Initial version                                                         - 04/02/2014 wer
 */
namespace Ritc\Library\Views;

use Ritc\Library\Helper\AuthHelper;
use Ritc\Library\Models\ConstantsModel;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Models\PeopleModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Traits\ViewTraits;

/**
 * Class ConstantsView
 * @class   ConstantsView
 * @package Ritc\Library\Views
 */
class ConstantsView
{
    use LogitTraits, ViewTraits;

    /** @var \Ritc\Library\Views\LibraryView */
    private $o_manager;
    /** @var \Ritc\Library\Models\ConstantsModel */
    private $o_model;
    /** @var \Ritc\Library\Models\PeopleModel */
    private $o_people;

    /**
     * ConstantsView constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupView($o_di);
        $this->o_manager = new LibraryView($o_di);
        $this->o_model   = new ConstantsModel($o_di->get('db'));
        $this->o_people  = new PeopleModel($o_di->get('db'));
        $this->o_auth    = new AuthHelper($o_di);
        if (DEVELOPER_MODE) {
            $this->o_elog = $o_di->get('elog');
            $this->o_model->setElog($this->o_elog);
        }
    }

    /**
     * Returns the list of configs in html.
     * @param array $a_message
     * @return string
     */
    public function renderList(array $a_message = array())
    {
        if (count($a_message) != 0) {
            $a_message['message'] .= "<br><br>Changing configuration values can result in unexpected results. If you are not sure, do not do it.";
        }
        else {
            $a_message = ViewHelper::warningMessage('Changing configuration values can result in unexpected results. If you are not sure, do not do it.');
        }
        $a_twig_values = $this->createDefaultTwigValues($a_message);
        $a_constants = $this->o_model->read([], ['order_by' => 'const_name']);
        if ($a_constants !== false && count($a_constants) > 0) {
            $a_twig_values['a_constants'] = $a_constants;
        }
        else {
            $a_twig_values['a_constants'] = [];
        }
        $tpl = $this->createTplString($a_twig_values);
        return $this->o_twig->render($tpl, $a_twig_values);
    }

    /**
     * Returns HTML verify form to delete.
     * @param array $a_values
     * @return string
     */
    public function renderVerify(array $a_values = array())
    {
        if ($a_values === array()) {
            $a_message = ViewHelper::messageProperties(['message' => 'An Error Has Occurred. Please Try Again.', 'type' => 'failure']);
            return $this->renderList($a_message);
        }
        $a_page_values = $this->getPageValues(); // provided in ViewTraits
        $a_twig_values = [
            'what'         => 'constant',
            'name'         => $a_values['constant']['const_name'],
            'where'        => 'constants',
            'btn_value'    => 'Constant',
            'hidden_name'  => 'const_id',
            'hidden_value' => $a_values['constant']['const_id'],
            'tolken'       => $a_values['tolken'],
            'form_ts'      => $a_values['form_ts'],
            'a_menus'      => $this->retrieveNav('ManagerLinks'),
            'twig_prefix'  => LIB_TWIG_PREFIX
        ];
        if (isset($a_values['public_dir'])) {
            $a_twig_values['public_dir'] = $a_values['public_dir'];
        }
        $a_twig_values = array_merge($a_twig_values, $a_page_values);
        $tpl = '@' . LIB_TWIG_PREFIX . 'pages/verify_delete.twig';
        return $this->o_twig->render($tpl, $a_twig_values);
    }
}