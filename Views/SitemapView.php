<?php
/**
 * Class SitemapView.
 * @package Ritc_Library
 */
namespace Ritc\Library\Views;

use Ritc\Library\Interfaces\ViewInterface;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigViewTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Various sitemap views.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0
 * @date    2018-05-26 12:50:04
 * @change_log
 * - v1.0.0 - Initial version                                   - 2018-05-26 wer
 */
class SitemapView implements ViewInterface
{
    use LogitTraits, ConfigViewTraits;

    /**
     * Sitemap constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupElog($o_di);
        $this->setupView($o_di);
    }

    /**
     * Main method to render the sitemap page.
     *
     * @return string
     */
    public function render():string
    {
        $a_message = [];
        $a_sitemap = $this->buildSitemapArray();
        if (!empty($a_sitemap['message'])) {
            $a_message = $a_sitemap;
            $a_sitemap = [];
        }
        $a_twig_values = $this->createDefaultTwigValues($a_message);
        $a_twig_values['a_sitemap'] = $a_sitemap;
        $tpl = $this->createTplString($a_twig_values);
        return $this->renderIt($tpl, $a_twig_values);
    }

}
