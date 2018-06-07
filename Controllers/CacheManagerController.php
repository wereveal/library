<?php
/**
 * Class CacheManagerController.
 * @package Ritc_Library
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ControllerInterface;
use Ritc\Library\Models\ConstantsModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigControllerTraits;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Views\CacheManagerView;

/**
 * Main router for Symfony based cache..
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0
 * @date    2018-06-06 11:30:29
 * @change_log
 * - v1.0.0         - Initial production     - 2018-06-06 wer
 * - v1.0.0-alpha.0 - Initial version        - 2018-05-30 wer
 */
class CacheManagerController implements ControllerInterface
{
    use LogitTraits, ConfigControllerTraits;

    /** @var int $cache_const_id */
    private $cache_const_id;
    /** @var bool|object  */
    private $o_cache;
    /** @var ConstantsModel $o_const */
    private $o_const;

    /**
     * CacheManagerController constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupElog($o_di);
        $this->setupManagerController($o_di);
        $o_cache = $o_di->get('cache');
        if (is_object($o_cache)) {
            $this->o_cache = $o_cache;
        }
        $this->setupConst();
    }

    /**
     * @return mixed|string
     */
    public function route()
    {
        $a_message = ViewHelper::infoMessage('The cache can speed things up but can also temporarily mask changes.');
        $message = 'Form Action: ' . $this->form_action;
        $this->logIt($message, LOG_ON, __METHOD__);
        switch ($this->form_action) {
            case 'clear_cache':
                if ($this->o_cache->clear()) {
                    $a_message = ViewHelper::successMessage();
                }
                else {
                    $a_message = ViewHelper::failureMessage('Could not clear the cache.');
                }
                break;
            case 'enable_cache':
                if ($this->updateCacheRecord('true')) {
                    $a_message = ViewHelper::successMessage();
                }
                else {
                    $a_message = ViewHelper::failureMessage('Could not enable the cache.');
                }
                break;
            case 'disable_cache':
                if ($this->updateCacheRecord('false')) {
                    $a_message = ViewHelper::successMessage();
                }
                else {
                    $a_message = ViewHelper::failureMessage('Could not disable the cache.');
                }
                $this->o_cache->clear();
                break;
            default:
                // do nothing
        }
        $o_view = new CacheManagerView($this->o_di);
        return $o_view->render($a_message);
    }

    /**
     * Sets up the model and sets the class property cache_const_id
     */
    private function setupConst()
    {
        $o_const = new ConstantsModel($this->o_db);
        $o_const->setupElog($this->o_di);
        $this->o_const = $o_const;
        try {
            $a_results = $o_const->read(['const_name' => 'USE_CACHE']);
            $this->cache_const_id = empty($a_results[0]['const_id'])
                ? -1
                : $a_results[0]['const_id']
            ;
        }
        catch (ModelException $e) {
            $this->cache_const_id = -1;
        }
    }

    /**
     * @param string $which_way
     * @return bool
     */
    private function updateCacheRecord($which_way = 'false')
    {
        if ($this->cache_const_id < 1) {
            return false;
        }
        $a_values = [
            'const_id'    => $this->cache_const_id,
            'const_value' => $which_way
        ];
        try {
            return $this->o_const->update($a_values);
        }
        catch (ModelException $e) {
            return false;
        }
    }

}
