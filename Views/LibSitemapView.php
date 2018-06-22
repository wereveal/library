<?php
/**
 * Class LibSitemapView.
 * @package Ritc_Library
 */
namespace Ritc\Library\Views;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\FormHelper;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ViewInterface;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigViewTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * View for the sitemap manager.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0
 * @date    2018-06-07 15:33:18
 * @change_log
 * - v1.0.0        - Initial version                            - 2018-06-07 wer
 */
class LibSitemapView implements ViewInterface
{
    use LogitTraits, ConfigViewTraits;

    /**
     * LibSitemapView constructor.
     *
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupView($o_di);
        $this->setupElog($o_di);
    }

    /**
     * Main method to render webpage.
     *
     * @param array $a_message
     * @return string
     */
    public function render(array $a_message = [])
    {
        $xml_cache_key       = 'nav.sitemap.for.xml';
        $list_cache_key      = 'nav.list.by.auth_level.0';
        $available_cache_key = 'nav.available.for.sitemap';
        $tag                 = 'nav';
        $a_results           = [];
        $a_nav_list          = [];
        $a_available         = [];
        if ($this->use_cache) {
            $a_results   = $this->o_cache->get($xml_cache_key);
            $a_nav_list  = $this->o_cache->get($list_cache_key);
            $a_available = $this->o_cache->get($available_cache_key);
        }
        if (empty($a_results)) {
            $a_results = $this->o_nav->getSitemapForXml();
            if ($this->use_cache) {
                $this->o_cache->set($xml_cache_key, $a_results, $tag);
            }
        }
        if (empty($a_nav_list)) {
            try {
                $a_nav_list = $this->o_nav->getNavListByAuthLevel(0);
                if ($this->use_cache) {
                    $this->o_cache->set($list_cache_key, $a_nav_list, 'nav');
                }
            }
            catch (ModelException $e) {
                $a_nav_list = [];
            }
        }
        if (empty($a_available)) {
            $btn_values = [
                'form_action' => '/manager/config/sitemap/',
                'btn_size'    => 'btn-sm',
                'hidden_name' => 'nav_id',
            ];
            foreach ($a_nav_list as $key => $item) {
                if (empty($a_available[$item['nav_name']])) {
                    $in_sitemap = false;
                    foreach ($a_results as $a_this) {
                        if ($a_this['loc'] == $item['url']) {
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
                $this->o_cache->set($available_cache_key, $a_available, 'nav');
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
    public function createXmlSitemap()
    {
        $xml_cache_key = 'nav.sitemap.for.xml';
        $a_sitemap = [];
        if ($this->use_cache) {
            $a_sitemap = $this->o_cache->get($xml_cache_key);
        }
        if (empty($a_sitemap)) {
            $a_sitemap = $this->o_nav->getSitemapForXml();
            if ($this->use_cache) {
                $this->o_cache->set($xml_cache_key, $a_sitemap, 'nav');
            }
        }
        $a_tpl_values = [
            'page_prefix' => LIB_TWIG_PREFIX,
            'twig_prefix' => TWIG_PREFIX,
            'twig_dir'    => 'pages',
            'tpl'         => 'sitemap_xml'
        ];
        $tpl = $this->createTplString($a_tpl_values);
        $file_contents = $this->renderIt($tpl, ['a_sitemap' => $a_sitemap]);
        $results = file_put_contents(PUBLIC_PATH . '/sitemap.xml', $file_contents);
        if ($results) {
            return ViewHelper::successMessage();
        }
        else {
            return ViewHelper::failureMessage();
        }
    }
}
