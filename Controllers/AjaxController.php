<?php
/**
 * Class AjaxController
 *
 * @package Ritc_Library
 */
namespace Ritc\Library\Controllers;

use JsonException;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Models\ConstantsModel;
use Ritc\Library\Models\TwigComplexModel;
use Ritc\Library\Models\TwigDirsModel;
use Ritc\Library\Models\TwigTemplatesModel;
use Ritc\Library\Models\UrlsModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ControllerTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Does Ajax Calls used by the Config manager.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v2.0.0
 * @date    2021-11-26 14:09:35
 * @change_log
 * - v2.0.0 - Updated for php 8                                 - 2021-11-26 wer
 * - v1.1.0 - Additional methods for the Page Manger            - 2018-09-29 wer
 * - v1.0.0 - Initial version                                   - 2018-04-10 wer
 */
class AjaxController
{
    use LogitTraits;
    use ControllerTraits;

    /**
     * AjaxController constructor.
     *
     * @param Di $o_di
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
     * @throws JsonException
     */
    public function route():string
    {
        return match ($this->url_action_one) {
            'twig_dirs' => $this->doTwigDirs(),
            'for_directories' => $this->forDirectories(),
            'urls_available' => $this->urlsAvailableForNavgroups(),
            'page_dirs_tpls' => $this->doTwigTpls(),
            'page_prefix_dirs' => $this->doTwigDirs(true),
            'content_vcs' => $this->updateContentVcsConstant(),
            default => json_encode([], JSON_THROW_ON_ERROR),
        };
    }

    /**
     * Creates the json string needed from list of twig directories based on twig_prefix.
     * This is for the javascript to create the options for a select.
     *
     * @param bool $has_tpls Optional, defaults to false.
     *                       Specifies if return only dirs that has templates.
     * @return string
     * @throws JsonException
     */
    private function doTwigDirs(bool $has_tpls = false):string
    {
        $prefix_id = $this->o_router->getPost('prefix_id');
        $bad_results = [
            'td_id' => '',
            'value' => 'Not Available'
        ];
        $bad_results = json_encode([$bad_results], JSON_THROW_ON_ERROR);
        if (empty($prefix_id)) {
            return $bad_results;
        }
        $cache_key = 'ajax.doTwigDirs.' . $prefix_id;
        if ($has_tpls) {
            $cache_key .= '.limited';
        }
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
            if ($has_tpls) {
                $o_tpls = new TwigTemplatesModel($this->o_db);
                foreach ($a_results as $key => $a_result) {
                    try {
                        $a_tpls = $o_tpls->read(['td_id' => $a_result['td_id']]);
                        if (empty($a_tpls[0])) {
                            unset($a_results[$key]);
                        }
                    }
                    catch (ModelException) {
                        unset($a_results[$key]);
                    }
                }
            }
            $a_encode_this = [];
            foreach ($a_results as $a_result) {
                $a_encode_this[] = [
                    'td_id'   => $a_result['td_id'],
                    'td_name' => $a_result['td_name']
                ];
            }
            $json = json_encode($a_encode_this, JSON_THROW_ON_ERROR);
            if ($this->use_cache) {
                $this->o_cache->set($cache_key, $json, 'ajax');
            }
            return $json;
        }
        catch (ModelException) {
            return $bad_results;
        }
    }

    /**
     * Creates the JSON string used by the javascript to set the
     * templates select options.
     *
     * @return string
     * @throws JsonException
     */
    private function doTwigTpls():string
    {
        $dir_id = $this->o_router->getPost('td_id');
        $bad_results = [
            'tpl_id' => '',
            'value'  => 'Not Available'
        ];
        $bad_results = json_encode([$bad_results], JSON_THROW_ON_ERROR);
        if (empty($dir_id)) {
            return $bad_results;
        }
        $cache_key = 'ajax.doTwigTpls.' . $dir_id;
        $json = '';
        if ($this->use_cache) {
            $json = $this->o_cache->get($cache_key);
        }
        if (!empty($json)) {
            return $json;
        }
        $o_tpls = new TwigTemplatesModel($this->o_db);
        try {
            $a_results = $o_tpls->read(['td_id' => $dir_id]);
            $a_encode_this = [];
            foreach ($a_results as $a_result) {
                $a_encode_this[] = [
                    'tpl_id'   => $a_result['tpl_id'],
                    'tpl_name' => $a_result['tpl_name']
                ];
            }
            $json = json_encode($a_encode_this, JSON_THROW_ON_ERROR);
            if ($this->use_cache) {
                $this->o_cache->set($cache_key, $json, 'ajax');
            }
            return $json;
        }
        catch (ModelException) {
            return $bad_results;
        }
    }

    /**
     * Creates the json string needed from list of twig directories based on twig_prefix.
     * This one is to display the list of directories to be modified or deleted.
     *
     * @return string
     * @throws JsonException
     */
    private function forDirectories():string
    {
        $prefix_id = $this->o_router->getPost('prefix_id');
        $bad_results = json_encode([[]], JSON_THROW_ON_ERROR);
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
            $json = json_encode($a_results, JSON_THROW_ON_ERROR);
            if ($this->use_cache) {
                $this->o_cache->set($cache_key, $json, 'ajax');
            }
            return $json;
        }
        catch (ModelException) {
            return $bad_results;
        }
        catch (JsonException) {
            return '';
        }
    }

    /**
     * Updates the constant CONTENT_VCS to be the opposite of what it is.
     *
     * @return bool
     */
    private function updateContentVcsConstant():bool
    {
        $o_constants = new ConstantsModel($this->o_db);
        $o_constants->setupElog($this->o_di);
        try {
            $a_results = $o_constants->selectByConstantName('CONTENT_VCS');
            $a_update_values = [
                'const_id' => $a_results['const_id'],
                'const_value' => $a_results['const_value'] === 'true' ? 'false' : 'true'
            ];
            $o_constants->update($a_update_values);
            return true;
        }
        catch (ModelException) {
            return false;
        }
    }

    /**
     * Create json string from a list of urls.
     * List consists of urls not in a navgroup. Assumes a url should only
     * be assigned to one navigation link.
     *
     * @return mixed
     * @throws JsonException
     */
    private function urlsAvailableForNavgroups(): mixed
    {
        $meth = __METHOD__ . '.';
        $navgroup_id = $this->o_router->getPost('navgroup_id');
        $cache_key = 'ajax.urlsAvailableFor.navgroup.' . $navgroup_id;
        $bad_results = [
            'url_id'   => '',
            'url_text' => 'Not Available'
        ];
        $results = '';
        $bad_results = json_encode([$bad_results], JSON_THROW_ON_ERROR);
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

                $results = trim(json_encode($a_encode_this, JSON_THROW_ON_ERROR));
                if ($this->use_cache) {
                    $this->o_cache->set($cache_key, $results, 'ajax');
                }
                $this->logIt('JSON: ' . $results, LOG_OFF, $meth . __LINE__);
            }
            catch (ModelException) {
                return $bad_results;
            }
            catch (JsonException) {
                return '';
            }
        }
        return $results;
    }
}
