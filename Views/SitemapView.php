<?php
/**
 * Class Sitemap.
 * @package Ritc_Library
 */
namespace Ritc\Library\Views;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ViewInterface;
use Ritc\Library\Models\NavComplexModel;
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
class Sitemap implements ViewInterface
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
     * @return string
     */
    public function render()
    {
        // TODO: Implement render() method.
        $a_sitemap = [];
        $a_message = [];
        if ($this->use_cache) {
            $date_key = 'sitemap.html.date';
            $value_key = 'sitemap.html.value';
            $date = $this->o_cache->get($date_key);
            if ($date == date('Ymd')) {
                $a_values = $this->o_cache->get($value_key);
                if (!empty($a_values)) {
                    $a_sitemap = $a_values;
                }
            }
        }
        if (empty($a_sitemap)) {
            $o_nav = new NavComplexModel($this->o_di);
            try {
                $a_sitemap = $o_nav->getSitemap();
            }
            catch (ModelException $e) {
                $a_message = ViewHelper::errorMessage('Unable to retrieve the sitemap.');
            }
        }
        $a_twig_values = $this->createDefaultTwigValues($a_message);
        $a_twig_values['a_sitemap'] = $a_sitemap;
        $tpl = $this->createTplString($a_twig_values);
        return $this->renderIt($tpl, $a_twig_values);
    }

    /**
     * Creates the sitemap.xml file used by search engines.
     * @param bool $force
     * @return bool|int
     */
    public function createXmlFile($force = false)
    {
        $key = 'sitemap.xml.date';
        if (!$force) {
            if ($this->use_cache) {
                $xml_create_date = $this->o_cache->get($key);
                if ($xml_create_date == date('Ymd')) {
                    return true;
                }
            }
        }
        if ($this->use_cache) {
            $this->o_cache->set($key, date('Ymd'));
        }
        $o_nav = new NavComplexModel($this->o_di);
        $a_sitemap = $o_nav->getSitemapForXml();
        $a_tpl_values = [
           'page_prefix' => LIB_TWIG_PREFIX,
           'twig_prefix' => TWIG_PREFIX,
           'twig_dir'    => 'pages',
           'tpl'         => 'sitemap_xml.twig'
        ];
        $tpl = $this->createTplString($a_tpl_values);
        $file_contents = $this->renderIt($tpl, ['a_sitemap' => $a_sitemap]);
        return file_put_contents(PUBLIC_PATH . '/sitemap.xml', $file_contents);
    }
}
