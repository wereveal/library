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

    /**
     * Renders the list of the twig prefixes, directories, and templates.
     * @param array $a_message
     * @return string
     */
    public function render(array $a_message = [])
    {
        $meth = __METHOD__ . '.';
        $o_tp = new TwigPrefixModel($this->o_db);
        $o_td = new TwigDirsModel($this->o_db);
        $o_tt = new TwigTemplatesModel($this->o_db);
        $o_tc = new TwigComplexModel($this->o_di);
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
                    $message = "Unable to retrieve the Twig prefix records";
                    $message = empty($a_message['message'])
                        ? $message
                        : $a_message['message'] . ' -- ' . $message;
                    ;
                    $a_message = ViewHelper::failureMessage($message);
                    $continue = false;
                }
            }
            catch (ModelException $e) {
                $message = "Unable to retrieve the Twig dir records";
                $message = empty($a_message['message'])
                    ? $message
                    : $a_message['message'] . ' -- ' . $message;
                ;
                $a_message = ViewHelper::failureMessage($message);
                $continue = false;
            }
        }
        catch (ModelException $e) {
            $message = "Unable to retrieve the Twig tpl records";
            $message = empty($a_message['message'])
                ? $message
                : $a_message['message'] . ' -- ' . $message;
            ;
            $a_message = ViewHelper::failureMessage($message);
            $continue = false;
        }
        if ($continue) {
            foreach ($a_tt_results as $key => $a_tt) {
                try {
                    $a_tc_results = $o_tc->readTplInfo($a_tt['tpl_id']);
                    $a_tt_results[$key]['twig_dir']    = $a_tc_results[0]['twig_dir'];
                    $a_tt_results[$key]['twig_prefix'] = $a_tc_results[0]['twig_prefix'];
                    $a_tt_results[$key]['tp_id']       = $a_tc_results[0]['tp_id'];
                }
                catch (ModelException $e) {
                    $message = 'Unable to read the template information';
                    $message = empty($a_message['message'])
                        ? $message
                        : $a_message['message'] . ' -- ' . $message;
                    ;
                    $a_message = ViewHelper::failureMessage($message);
                    $continue = false;
                    break;
                }
            }
            if ($continue) {
                $a_sort_order = [
                    'tp_id'    => 'ASC',
                    'td_id'    => 'ASC',
                    'tpl_name' => 'ASC'
                ];
                $a_tt_results = Arrays::multiSort($a_tt_results, $a_sort_order);
                $log_message = 'a_tt_results ' . var_export($a_tt_results, true);
                $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
            }
        }
        $a_twig_values = $this->createDefaultTwigValues($a_message, '/manager/config/twig/');
        $a_twig_values['a_tpls']   = $a_tt_results;
        $a_twig_values['a_dirs']   = $a_td_results;
        $a_twig_values['a_prefix'] = $a_tp_results;

        $tpl = $this->createTplString($a_twig_values);
        $log_message = 'twig values ' . var_export($a_twig_values, true);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
        $this->logIt('TPL: ' . $tpl, LOG_OFF, $meth . __LINE__);
        return $this->renderIt($tpl, $a_twig_values);
    }

    /**
     * Returns the verify delete form with directory values.
     * @param int $td_id
     * @return string
     */
    public function renderDeleteDir($td_id = -1)
    {
        if ($td_id == -1) {
            $a_message = ViewHelper::warningMessage('Unable to delete the directory: a directory must be specified by id.');
            return $this->render($a_message);
        }
        $a_router_parts = $this->o_router->getRouteParts();
        $o_tc = new TwigComplexModel($this->o_di);
        if (!$o_tc->canBeDeleted('td', $td_id)) {
            $a_message = ViewHelper::warningMessage('Unable to delete the directory: a template may still use it.');
            return $this->render($a_message);
        }
        $o_dir = new TwigDirsModel($this->o_db);
        try {
            $a_results = $o_dir->read(['td_id' => $td_id]);
            if (empty($a_results)) {
                $a_message = ViewHelper::errorMessage('Unable to delete the directory: an error occurred.');
                return $this->render($a_message);
            }
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::errorMessage('Unable to delete the directory: an error occurred.');
            return $this->render($a_message);
        }
        $a_values = [
            'what'         => 'Directory',
            'name'         => $a_results[0]['td_name'],
            'form_action'  => '/manager/config/twig/',
            'submit_value' => 'delete_dir',
            'btn_value'    => 'Delete',
            'hidden_name'  => 'td_id',
            'hidden_value' => $a_results[0]['td_id']
        ];
        $a_options = [
            'tpl'       => 'verify_delete',
            'a_message' => [],
            'fallback'  => 'render',
            'location'  => $a_router_parts['route_path']
        ];
        return $this->renderVerifyDelete($a_values, $a_options);
    }

    /**
     * Returns the verify delete form with prefix values.
     * @param int $tp_id
     * @return string
     */
    public function renderDeleteTp($tp_id = -1)
    {
        if ($tp_id == -1) {
            $a_message = ViewHelper::warningMessage('Unable to delete the twig prefix: it must be specified by id.');
            return $this->render($a_message);
        }
        $a_router_parts = $this->o_router->getRouteParts();
        $o_tc = new TwigComplexModel($this->o_di);
        if (!$o_tc->canBeDeleted('tp', $tp_id)) {
            $a_message = ViewHelper::errorMessage("Unable to delete the twig prefix: directories which use it may still exist.");
            return $this->render($a_message);
        }
        $o_tp = new TwigPrefixModel($this->o_db);
        try {
            $a_results = $o_tp->read(['tp_id' => $tp_id]);
            if (!empty($a_results)) {
                $a_tp = $a_results[0];
            }
            else {
                $a_message = ViewHelper::warningMessage("Unable to delete the twig prefix: the prefix values were not available.");
                return $this->render($a_message);
            }
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::errorMessage("Unable to delete the twig prefix: an error has occurred.");
            return $this->render($a_message);
        }
        /* if a directory still uses this twig prefix, don't allow delete */
        $a_values = [
            'what'         => 'Twig Prefix',
            'name'         => $a_tp['tp_prefix'],
            'form_action'  => '/manager/config/twig/',
            'submit_value' => 'delete_tp',
            'btn_value'    => 'Delete',
            'hidden_name'  => 'tp_id',
            'hidden_value' => $a_tp['tp_id']
        ];
        $a_options = [
            'tpl'       => 'verify_delete',
            'a_message' => [],
            'fallback'  => 'render',
            'location'  => $a_router_parts['route_path']
        ];
        return $this->renderVerifyDelete($a_values, $a_options);
    }

    /**
     * Renders the verify delete form with template variables.
     * @param int $tpl_id
     * @return string
     */
    public function renderDeleteTpl($tpl_id = -1)
    {
        if ($tpl_id == -1) {
            $a_message = ViewHelper::warningMessage('Unable to delete the template: it must be specified by id.');
            return $this->render($a_message);
        }
        $a_router_parts = $this->o_router->getRouteParts();
        // first make sure no pages still use this template
        $o_tc = new TwigComplexModel($this->o_di);
        if (!$o_tc->canBeDeleted('tpl', $tpl_id)) {
            $a_message = ViewHelper::warningMessage("Unable to delete the template: a page may still use it.");
            return $this->render($a_message);
        }
        $o_tpl  = new TwigTemplatesModel($this->o_db);
        try {
            $a_results = $o_tpl->read(['tpl_id' => $tpl_id]);
            if (count($a_results) > 0) {
                $a_tpl = $a_results[0];
            }
            else {
                $a_message = ViewHelper::warningMessage("Unable to delete the template: the template values were not available.");
                return $this->render($a_message);
            }
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::errorMessage("Unable to delete the template: could not find it.");
            return $this->render($a_message);
        }
        $login_id = $this->o_session->getVar('login_id');
        if ($a_tpl['tpl_immutable'] == 'true' && $this->o_auth->hasMinimumAuthLevel($login_id, 9)) {
            $a_message = ViewHelper::warningMessage("Unable to delete the template: the template is immutable.");
            return $this->render($a_message);
        }
        $a_values = [
            'what'         => 'Template',
            'name'         => $a_tpl['tpl_name'],
            'form_action'  => '/manager/config/twig/',
            'submit_value' => 'delete_tpl',
            'btn_value'    => 'Delete',
            'hidden_name'  => 'tpl_id',
            'hidden_value' => $a_tpl['tpl_id']
        ];
        $a_options = [
            'tpl'       => 'verify_delete',
            'a_message' => [],
            'fallback'  => 'render',
            'location'  => $a_router_parts['route_path']
        ];
        return $this->renderVerifyDelete($a_values, $a_options);
    }

}

