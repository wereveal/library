<?php
/**
 * @brief     View for the Twig manager.
 * @details
 * @ingroup   lib_views
 * @file      Ritc/Library/Views/TwigView.php
 * @namespace Ritc\Library\Views
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.0
 * @date      2017-05-14 16:49:48
 * @note Change Log
 * - v1.0.0-alpha.0 - Initial version        - 2017-05-14 wer
 * @todo Ritc/Library/Views/TwigView.php - Everything
 */
namespace Ritc\Library\Views;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\Arrays;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ViewInterface;
use Ritc\Library\Models\TwigComplexModel;
use Ritc\Library\Models\TwigDirsModel;
use Ritc\Library\Models\TwigPrefixModel;
use Ritc\Library\Models\TwigTemplatesModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigViewTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class TwigView.
 * @class   TwigView
 * @package Ritc\Library\Views
 */
class TwigView implements ViewInterface
{
    use ConfigViewTraits, LogitTraits;

    public function __construct(Di $o_di)
    {
        $this->setupElog($o_di);
        $this->setupView($o_di);
    }

    public function render()
    {
        $meth = __METHOD__ . '.';
        $o_tp = new TwigPrefixModel($this->o_db);
        $o_td = new TwigDirsModel($this->o_db);
        $o_tt = new TwigTemplatesModel($this->o_db);
        $o_tc = new TwigComplexModel($this->o_di);
        $a_message = [];
        $continue = true;
        $a_tt_results = [];
        $a_td_results = [];
        $a_tp_results = [];
        try {
            $a_tt_results = $o_tt->read();
            try {
                $a_td_results = $o_td->read();
                try {
                    $a_tp_results = $o_tp->read();
                }
                catch (ModelException $e) {
                    $a_message = ViewHelper::failureMessage("Unable to retrieve the Twig prefix records");
                    $continue = false;
                }
            }
            catch (ModelException $e) {
                $a_message = ViewHelper::failureMessage("Unable to retrieve the Twig dir records");
                $continue = false;
            }
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::failureMessage("Unable to retrieve the Twig tpl records");
            $continue = false;
        }
        if ($continue) {
            foreach ($a_tt_results as $key => $a_tt) {
                try {
                    $a_tc_results = $o_tc->readTplInfo($a_tt['tpl_id']);
                    $a_tt_results[$key]['twig_dir'] = $a_tc_results[0]['twig_dir'];
                    $a_tt_results[$key]['twig_prefix'] = $a_tc_results[0]['twig_prefix'];
                    $a_tt_results[$key]['tp_id'] = $a_tc_results[0]['tp_id'];
                }
                catch (ModelException $e) {
                    $a_message = ViewHelper::failureMessage('Unable to read the template information');
                    break;
                }
            }
        }
        if ($continue) {
            // sort the tt_results
            $log_message = 'tt results ' . var_export($a_tt_results, TRUE);
            $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

            $a_tt_resorted = Arrays::multiSort($a_tt_results, ['tp_id' => 'ASC', 'td_id' => 'ASC', 'tpl_name' => 'ASC']);
            $log_message = 'a_tt_resorted ' . var_export($a_tt_resorted, TRUE);
            $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        }
        $a_twig_values = $this->createDefaultTwigValues($a_message, '/manager/config/twig/');
        $a_twig_values['a_tpls'] = $a_tt_resorted;
        $a_twig_values['a_dirs'] = $a_td_results;
        $a_twig_values['a_prefix'] = $a_tp_results;
        $tpl = $this->createTplString($a_twig_values);
        $log_message = 'twig values ' . var_export($a_twig_values, true);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
        return $this->renderIt($tpl, $a_twig_values);
    }
}