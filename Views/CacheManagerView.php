<?php
/**
 * Class CacheManagerView.
 * @package Ritc_Library
 */
namespace Ritc\Library\Views;

use Ritc\Library\Interfaces\ViewInterface;
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

    public function render()
    {
        // TODO: Implement render() method.
    }
}
