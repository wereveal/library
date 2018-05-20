<?php
namespace Ritc\Library\Controllers;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Models\TwigComplexModel;
use Ritc\Library\Models\TwigDirsModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ControllerTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class AjaxController - Does Ajax Calls used by the Config manager.
 *
 * @package RITC_Library
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-alpha.0
 * @date    2018-04-10 11:14:16
 * ## Change Log
 * - v1.0.0-alpha.0 - Initial version        - 2018-04-10 wer
 */
class AjaxController
{
    use ControllerTraits, LogitTraits;

    /**
     * AjaxController constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupController($o_di);
    }

    /**
     * Main router for class.
     * @return string
     */
    public function route()
    {
        switch ($this->url_action_one) {
            case 'twig_dirs';
                return $this->doTwigDirs();
            case 'forDirectories';
                return $this->forDirectories();
            default:
                return json_encode([]);
        }
    }

    /**
     * Creates the json string needed from list of twig directories based on twig_prefix.
     * This is for the javascript to create the options for a select.
     * @return string
     */
    private function doTwigDirs()
    {
        $prefix_id = $this->o_router->getPost('prefix_id');
        $bad_results = [
            'td_id' => '',
            'value' => 'Not Available'
        ];
        if (empty($prefix_id)) {
            return json_encode([$bad_results]);
        }
        $o_dirs = new TwigDirsModel($this->o_db);
        try {
            $a_results = $o_dirs->read(['tp_id' => $prefix_id]);
            $a_encode_this = [];
            foreach ($a_results as $a_result) {
                $a_encode_this[] = [
                    'td_id'   => $a_result['td_id'],
                    'td_name' => $a_result['td_name']
                ];
            }
            return json_encode($a_encode_this);
        }
        catch (ModelException $e) {
            return json_encode($bad_results);
        }
    }

    /**
     * Creates the json string needed from list of twig directories based on twig_prefix.
     * This one is to display the list of directories to be modified or deleted.
     * @return string
     */
    private function forDirectories()
    {
        $prefix_id = $this->o_router->getPost('prefix_id');
        $bad_results = [];
        if (empty($prefix_id)) {
            return json_encode([$bad_results]);
        }
        $o_tc = new TwigComplexModel($this->o_di);
        try {
            $a_results = $o_tc->readDirsForPrefix($prefix_id);
            foreach ($a_results as $key => $values) {
                $a_results[$key]['tolken']  = $_SESSION['token'];
                $a_results[$key]['form_ts'] = $_SESSION['idle_timestamp'];
            }
            return json_encode($a_results);
        }
        catch (ModelException $e) {
            return json_encode([$bad_results]);
        }
    }
}
