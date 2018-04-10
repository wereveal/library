<?php
/**
 * @brief     Does Ajax Calls used by the Config manager.
 * @details
 * @ingroup   lib_controllers
 * @file      Ritc/Library/Controllers/AjaxController.php
 * @namespace Ritc\Library\Controllers
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.0
 * @date      2018-04-10 11:14:16
 * @note Change Log
 * - v1.0.0-alpha.0 - Initial version        - 2018-04-10 wer
 * @todo Ritc/Library/Controllers/AjaxController.php - Everything
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Models\TwigDirsModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ControllerTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class AjaxController.
 * @class   AjaxController
 * @package Ritc\Library\Controllers
 */
class AjaxController
{
    use ControllerTraits, LogitTraits;

    public function __construct(Di $o_di)
    {
        $this->setupController($o_di);
    }

    public function route()
    {
        switch ($this->url_action_one) {
            case 'twig_dirs';
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
            default:
                return json_encode([]);
        }
    }
}