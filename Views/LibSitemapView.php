<?php
/**
 * Class LibSitemapView.
 * @package Ritc_Library
 */
namespace Ritc\Library\Views;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\FormHelper;
use Ritc\Library\Helper\Strings;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ViewInterface;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigViewTraits;

/**
 * View for the sitemap manager.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 2.0.0
 * @date    2021-12-01 13:14:23
 * @change_log
 * - 2.0.0 - updated to php8 standards                          - 2021-12-01 wer
 * - 1.0.0 - Initial version                                    - 2018-06-07 wer
 */
class LibSitemapView implements ViewInterface
{
    use ConfigViewTraits;

    /**
     * LibSitemapView constructor.
     *
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupView($o_di);
    }

    /**
     * Main method to render webpage.
     *
     * @param array $a_message
     * @return string
     */
    public function render(array $a_message = []):string
    {
        $xml_cache_key       = 'nav.sitemap.for.xml';
        $list_cache_key      = 'nav.list.by.auth_level.0';
        $available_cache_key = 'nav.available.for.sitemap';
        $a_results           = [];
        $a_nav_list          = [];
        $a_available         = [];
        if ($this->use_cache) {
            $a_results   = $this->o_cache->get($xml_cache_key);
            $a_results   = json_decode($a_results, true);
            $a_nav_list  = $this->o_cache->get($list_cache_key);
            $a_nav_list  = json_decode($a_nav_list, true);
            $a_available = $this->o_cache->get($available_cache_key);
            $a_available = json_decode($a_available, true);
        }
        if (empty($a_results)) {
            $a_results = $this->o_nav->getSitemapForXml();
            if ($this->use_cache) {
                $cache_value = Strings::arrayToJsonString($a_results);
                $this->o_cache->set($xml_cache_key, $cache_value);
            }
        }
        if (empty($a_nav_list)) {
            try {
                $a_nav_list = $this->o_nav->getNavListByAuthLevel();
                if ($this->use_cache) {
                    $cache_value = Strings::arrayToJsonString($a_nav_list);
                    $this->o_cache->set($list_cache_key,  $cache_value);
                }
            }
            catch (ModelException) {
                $a_nav_list = [];
            }
        }
        if (empty($a_available)) {
            $btn_values = [
                'form_action' => '/manager/config/sitemap/',
                'btn_size'    => 'btn-sm',
                'hidden_name' => 'nav_id'
            ];
            foreach ($a_nav_list as $item) {
                if (empty($a_available[$item['nav_name']])) {
                    $in_sitemap = false;
                    foreach ($a_results as $a_this) {
                        if ($a_this['loc'] === $item['url']) {
                            $in_sitemap = true;
                        }
                    }
                    $btn_values['hidden_value'] = $item['nav_id'];
                    if ($in_sitemap) {
                        $btn_values['btn_value'] = 'remove_from';
                        $btn_values['btn_label'] = 'Remove';
                    }
                    else {
                        $btn_values['btn_value'] = 'add_to';
                        $btn_values['btn_label'] = 'Add';
                    }
                    $item['action_btn'] = FormHelper::singleBtnForm($btn_values);
                    $a_available[$item['nav_name']] = $item;
                }
            }
            if ($this->use_cache) {
                $cache_value = Strings::arrayToJsonString($a_available);
                $this->o_cache->set($available_cache_key,  $cache_value);
            }
        }
        $rebuild_btn = [
            'form_action' => '/manager/config/sitemap/',
            'btn_value'   => 'build_xml',
            'btn_label'   => 'Build XML Sitemap'
        ];
        $a_twig_values = $this->createDefaultTwigValues($a_message);
        $a_twig_values['rebuild_btn'] = FormHelper::singleBtnForm($rebuild_btn);
        $a_twig_values['a_sitemap']   = $a_results;
        $a_twig_values['a_available'] = $a_available;
        $tpl = $this->createTplString($a_twig_values);
        return $this->renderIt($tpl, $a_twig_values);
    }

    /**
     * Creates the sitemap.xml file.
     *
     * @return array
     */
    public function createXmlSitemap():array
    {
        $xml_cache_key = 'nav.sitemap.for.xml';
        $a_sitemap = [];
        if ($this->use_cache) {
            $cache_value = $this->o_cache->get($xml_cache_key);
            $a_sitemap = json_decode($cache_value, true);
        }
        if (empty($a_sitemap)) {
            $a_sitemap = $this->o_nav->getSitemapForXml();
            if ($this->use_cache) {
                $cache_value = Strings::arrayToJsonString($a_sitemap);
                $this->o_cache->set($xml_cache_key,  $cache_value);
            }
        }
        $a_tpl_values = [
            'page_prefix' => LIB_TWIG_PREFIX,
            'site_prefix' => SITE_PREFIX,
            'twig_dir'    => 'pages',
            'tpl'         => 'sitemap_xml'
        ];
        $tpl = $this->createTplString($a_tpl_values);
        $file_contents = $this->renderIt($tpl, ['a_sitemap' => $a_sitemap]);
        $results = file_put_contents(PUBLIC_PATH . '/sitemap.xml', $file_contents);
        if ($results) {
            return ViewHelper::successMessage();
        }

        return ViewHelper::failureMessage();
    }
}
