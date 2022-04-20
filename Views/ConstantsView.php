<?php
/**
 * Class ConstantsView
 * @package Ritc_Library
 */
namespace Ritc\Library\Views;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Models\ConstantsModel;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigViewTraits;

/**
 * View for the Configuration page.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 3.0.0
 * @date    2021-12-01 13:04:39
 * @change_log
 * - 3.0.0   - updated to php 8                                                        - 2021-12-01 wer
 * - 2.2.0   - refactoring elsewhere made changes here.                                - 2017-06-20 wer
 * - 2.1.0   - Updated to match ViewTraits and fix misc bugs.                          - 2017-06-07 wer
 * - 2.0.0   - Name refactoring                                                        - 2017-05-14 wer
 * - 1.2.4   - Refactored the tpls to implement LIB_TWIG_PREFIX pushed changes here    - 2016-04-11 wer
 * - 1.2.2   - Implement LIB_TWIG_PREFIX                                               - 12/12/2015 wer
 * - 1.2.0   - Immutable code added                                                    - 10/07/2015 wer
 * - 1.1.0   - removed abstract class Base, added LogitTraits                          - 09/01/2015 wer
 * - 1.0.0   - first fully working version                                             - 01/28/2015 wer
 * - 1.0.0β3 - changed to use the new Di class                                         - 11/17/2014 wer
 * - 1.0.0β2 - changed to use Base class and inject database object                    - 09/24/2014 wer
 * - 1.0.0β1 - Initial version                                                         - 04/02/2014 wer
 */
class ConstantsView
{
    use ConfigViewTraits;

    /** @var LibraryView */
    private LibraryView $o_view;

    /**
     * ConstantsView constructor.
     *
     * @param Di $o_di
     * @noinspection UnusedConstructorDependenciesInspection
     */
    public function __construct(Di $o_di)
    {
        $this->setupView($o_di);
        $this->o_view = new LibraryView($o_di);
    }

    /**
     * Returns the list of configs in html.
     *
     * @param array $a_message
     * @return string
     */
    public function renderList(array $a_message = []):string
    {
        $message = 'Changing configuration values can result in unexpected results. If you are not sure, do not do it.';
        if (empty($a_message)) {
            $a_message = ViewHelper::warningMessage($message);
        }
        else {
            $a_message = ViewHelper::addMessage($a_message, $message);
        }
        $cache_key = 'constants.read.all';
        $a_constants = [];
        if ($this->use_cache) {
            $a_constants = $this->o_cache->get($cache_key);
        }
        if (empty($a_constants)) {
            $o_const = new ConstantsModel($this->o_db);
            try {
                $a_constants = $o_const->read([], ['order_by' => 'const_name']);
                if ($this->use_cache) {
                    $this->o_cache->set($cache_key, $a_constants, 'constants');
                }
            }
            catch (ModelException) {
                $a_twig_values['a_constants'] = [];
            }
        }
        $a_twig_values = $this->createDefaultTwigValues($a_message);
        $a_twig_values['a_constants'] = $a_constants;
        $tpl = $this->createTplString($a_twig_values);
        return $this->renderIt($tpl, $a_twig_values);
    }
}
