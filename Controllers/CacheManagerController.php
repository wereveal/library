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
 * @version 1.1.0
 * @date    2021-11-26 14:21:32
 * @change_log
 * - v1.1.0         - Updated for php 8                         - 2021-11-26 wer
 * - v1.0.0         - Initial production                        - 2018-06-06 wer
 * - v1.0.0-alpha.0 - Initial version                           - 2018-05-30 wer
 */
class CacheManagerController implements ControllerInterface
{
    use LogitTraits;
    use ConfigControllerTraits;

    /** @var int $cache_const_id */
    private int $cache_const_id;
    /** @var ConstantsModel $o_const */
    private ConstantsModel $o_const;

    /**
     * CacheManagerController constructor.
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupElog($o_di);
        $this->setupManagerController($o_di);
        $this->setupConst();
    }

    /**
     * @return string
     */
    public function route(): string
    {
        $a_message = ViewHelper::infoMessage('The cache can speed things up but can also temporarily mask changes.');
        $message = 'Form Action: ' . $this->form_action;
        $this->logIt($message, LOG_OFF, __METHOD__);
        switch ($this->form_action) {
            case 'clear_cache':
                if ($this->o_cache->clearAll()) {
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
                    $msg = 'Could not enable the cache. ';
                    $msg .= !ini_get('opcache.enable')
                        ? 'Opcache is not enabled.'
                        : 'USE_CACHE record not found.';
                    $a_message = ViewHelper::failureMessage($msg);
                }
                break;
            case 'disable_cache':
                if ($this->updateCacheRecord()) {
                    $a_message = ViewHelper::successMessage();
                }
                else {
                    $a_message = ViewHelper::failureMessage('Could not disable the cache.');
                }
                $this->o_cache->clearAll();
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
    private function setupConst():void
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
        catch (ModelException) {
            $this->cache_const_id = -1;
        }
    }

    /**
     * @param string $which_way
     * @return bool|null
     */
    private function updateCacheRecord(string $which_way = 'false'):?bool
    {
        if ($this->cache_const_id < 1 || !ini_get('opcache.enable')) {
            return false;
        }
        $a_values = [
            'const_id'    => $this->cache_const_id,
            'const_value' => $which_way
        ];
        try {
            return $this->o_const->update($a_values);
        }
        catch (ModelException) {
            return false;
        }
    }

}
