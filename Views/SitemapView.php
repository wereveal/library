<?php
/**
 * Class Sitemap.
 * @package Ritc_Library
 */
namespace Ritc\Library\Views;

use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigViewTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Various sitemap views.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.0
 * @date    2018-05-26 12:50:04
 * @change_log
 * - v1.0.0-alpha.0 - Initial version        - 2018-05-26 wer
 * @todo Sitemap.php - Everything
 */
class Sitemap
{
    use LogitTraits, ConfigViewTraits;

    public function __construct(Di $o_di)
    {
        $this->setupElog($o_di);
        $this->setupView($o_di);
    }

    public function createXmlFile()
    {
        $file_contents = '';
        // get links
        // create array compatible with template
        // render xml
        file_put_contents(PUBLIC_PATH . '/sitemap.xml', $file_contents);
    }
}
