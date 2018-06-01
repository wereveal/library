<?php
/**
 * Class CacheManagerView.
 * @package Ritc_Library
 */
namespace Ritc\Library\Views;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ViewInterface;
use Ritc\Library\Models\ConstantsModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigViewTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Manager for Symfony based cache..
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.0
 * @date    2018-05-30 15:43:15
 * @change_log
 * - v1.0.0-alpha.0 - Initial version        - 2018-05-30 wer
 * @todo CacheManagerView.php - Everything
 */
class CacheManagerView implements ViewInterface
{
    use LogitTraits, ConfigViewTraits;

    public function __construct(Di $o_di)
    {
        $this->setupElog($o_di);
        $this->setupView($o_di);
    }

    /**
     * Renders forms for managing the cache.
     * @return string
     */
    public function render(array $a_message = [])
    {
        $o_constants = new ConstantsModel($this->o_db);
        $o_constants->setupElog($this->o_di);
        $is_enabled = 'unknown';
        try {
            $a_results = $o_constants->read(['const_name' => 'USE_CACHE']);
            if (empty($a_results[0]['const_value'])) {
                $is_enabled = 'unknown';
                $a_message = ViewHelper::errorMessage('Unable to determine if the cache is enabled.<br>Manually set it with the constants manager.');
            }
            if ($a_results[0]['const_value'] == 'true') {
                $is_enabled = 'true';
            }
            else {
                $is_enabled = 'false';
            }
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::errorMessage('Unable to determine if the cache is enabled.<br>Manually set it with the constants manager.');
        }
        $log_message = 'message: ' . var_export($a_message, TRUE);
        $this->logIt($log_message, LOG_OFF, __METHOD__);

        $a_twig_values = $this->createDefaultTwigValues($a_message);
        $a_twig_values['is_enabled'] = $is_enabled;
        $tpl = $this->createTplString($a_twig_values);
        $log_message = 'final twig values ' . var_export($a_twig_values, TRUE);
        $this->logIt($log_message, LOG_OFF, __METHOD__);

        return $this->renderIt($tpl, $a_twig_values);
    }
}
