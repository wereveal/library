<?php
/**
 * Class AjaxController
 *
 * @package Ritc_Library
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Models\TwigComplexModel;
use Ritc\Library\Models\TwigDirsModel;
use Ritc\Library\Models\UrlsModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ControllerTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Does Ajax Calls used by the Config manager.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0
 * @date    2018-04-10 11:14:16
 * @change_log
 * - v1.0.0 - Initial version                                   - 2018-04-10 wer
 */
class AjaxController
{
    use ControllerTraits, LogitTraits;

    /**
     * AjaxController constructor.
     *
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupController($o_di);
        $this->setupElog($o_di);
    }

    /**
     * Main router for class.
     *
     * @return string
     */
    public function route():string
    {
        switch ($this->url_action_one) {
            case 'twig_dirs';
                return $this->doTwigDirs();
            case 'for_directories';
                return $this->forDirectories();
            case 'urls_available':
                return $this->urlsAvailableForNavgroups();
            default:
                return json_encode([]);
        }
    }

    /**
     * Creates the json string needed from list of twig directories based on twig_prefix.
     * This is for the javascript to create the options for a select.
     *
     * @return string
     */
    private function doTwigDirs():string
    {
        $prefix_id = $this->o_router->getPost('prefix_id');
        $bad_results = [
            'td_id' => '',
            'value' => 'Not Available'
        ];
        $bad_results = json_encode([$bad_results]);
        if (empty($prefix_id)) {
            return $bad_results;
        }
        $cache_key = 'ajax.doTwigDirs.' . $prefix_id;
        $json = '';
        if ($this->use_cache) {
            $json = $this->o_cache->get($cache_key);
        }
        if (!empty($json)) {
            return $json;
        }
        $o_dirs = new TwigDirsModel($this->o_db);
        try {
            $a_results = $o_dirs->read(['tp_id' => $prefix_id]);
            $a_encode_this = [];
            foreach ($a_results as $a_result) {
                $a_encode_this[] = [
                    'td_id'   => $a_result['td_id'],
                    'td_name' => $a_result['td_name']
                ];
            }
            $json = json_encode($a_encode_this);
            if ($this->use_cache) {
                $this->o_cache->set($cache_key, $json, 'ajax');
            }
            return $json;
        }
        catch (ModelException $e) {
            return $bad_results;
        }
    }

    /**
     * Creates the json string needed from list of twig directories based on twig_prefix.
     * This one is to display the list of directories to be modified or deleted.
     *
     * @return string
     */
    private function forDirectories():string
    {
        $prefix_id = $this->o_router->getPost('prefix_id');
        $bad_results = json_encode([[]]);
        if (empty($prefix_id)) {
            return $bad_results;
        }
        $cache_key = 'ajax.forDirectories.' . $prefix_id;
        $json = '';
        if ($this->use_cache) {
            $json = $this->o_cache->get($cache_key);
        }
        if (!empty($json)) {
            return $json;
        }
        $o_tc = new TwigComplexModel($this->o_di);
        try {
            $a_results = $o_tc->readDirsForPrefix($prefix_id);
            foreach ($a_results as $key => $values) {
                $a_results[$key]['tolken']  = $_SESSION['token'];
                $a_results[$key]['form_ts'] = $_SESSION['idle_timestamp'];
            }
            $json = json_encode($a_results);
            if ($this->use_cache) {
                $this->o_cache->set($cache_key, $json, 'ajax');
            }
            return $json;
        }
        catch (ModelException $e) {
            return $bad_results;
        }
    }

    /**
     * Create json string from a list of urls.
     * List consists of urls not in a navgroup. Assumes a url should only
     * be assigned to one navigation link.
     *
     * @return array|mixed|string
     */
    private function urlsAvailableForNavgroups()
    {
        $meth = __METHOD__ . '.';
        $navgroup_id = $this->o_router->getPost('navgroup_id');
        $cache_key = 'ajax.urlsAvailableFor.navgroup.' . $navgroup_id;
        $bad_results = [
            'url_id'   => '',
            'url_text' => 'Not Available'
        ];
        $results = '';
        $bad_results = json_encode([$bad_results]);
        if (empty($navgroup_id)) {
            return $bad_results;
        }
        if ($this->use_cache) {
            $results = $this->o_cache->get($cache_key);
        }
        if (empty($results)) {
            $o_urls = new UrlsModel($this->o_db);
            $a_encode_this = [[
                'url_id'   => '',
                'url_text' => '--Select URL--'
            ]];
            try {
                $a_results = $o_urls->readNotInNavgroup($navgroup_id);
                foreach ($a_results as $a_result) {
                    $a_encode_this[] = [
                      'url_id'   => $a_result['url_id'],
                      'url_text' => $a_result['url_text']
                    ];
                }
                  $log_message = 'urls results ' . var_export($a_encode_this, true);
                  $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

                $results = trim(json_encode($a_encode_this));
                if ($this->use_cache) {
                    $this->o_cache->set($cache_key, $results, 'ajax');
                }
                $this->logIt('JSON: ' . $results, LOG_ON, $meth . __LINE__);
            }
            catch (ModelException $e) {
                return $bad_results;
            }
        }
        return $results;
    }
}
