<?php
/**
 * Class BlocksView.
 * @package Ritc_Library
 */
namespace Ritc\Library\Views;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ViewInterface;
use Ritc\Library\Models\BlocksModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigViewTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * View class for the blocks manager.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.0
 * @date    2018-06-03 16:45:44
 * @change_log
 * - v1.0.0-alpha.0 - Initial version        - 2018-06-03 wer
 * @todo BlocksView.php - Everything
 */
class BlocksView implements ViewInterface
{
    use LogitTraits, ConfigViewTraits;

    /**
     * BlocksView constructor.
     *
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupView($o_di);
        $this->setupElog($o_di);
    }

    /**
     * Main render method for view.
     *
     * @param array $a_message
     * @return string
     */
    public function render(array $a_message = []):string
    {
        $meth = __METHOD__ . '.';
        $cache_key = 'blocks.read.all';
        if ($this->use_cache) {
            $results = $this->o_cache->get($cache_key);
        }
        if (empty($results)) {
            $o_model = new BlocksModel($this->o_db);
            $o_model->setupElog($this->o_di);
            try {
                $results = $o_model->read([],['order_by' => 'b_name ASC']);
                if ($this->use_cache) {
                    $this->o_cache->set($cache_key, $results, 'blocks');
                }
            }
            catch (ModelException $e) {
                $a_message = ViewHelper::addMessage($a_message, $e->getMessage(), 'error');
                $results = [];
            }
        }
        $log_message = 'results ' . var_export($results, true);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        $a_twig_values = $this->createDefaultTwigValues($a_message);
        $a_twig_values['a_blocks'] = $results;
        $tpl = $this->createTplString($a_twig_values);
        return $this->renderIt($tpl, $a_twig_values);
    }
}
