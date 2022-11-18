<?php
/**
 * Class ContentView.
 *
 * @package Ritc_Library
 */

namespace Ritc\Library\Views;

use JsonException;
use Ritc\Library\Exceptions\CacheException;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Exceptions\ViewException;
use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ViewInterface;
use Ritc\Library\Models\ContentComplexModel;
use Ritc\Library\Models\PageBlocksMapModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigViewTraits;

/**
 * Manager for Content View.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.2
 * @date    2022-05-13 15:58:45
 * @change_log
 * - 1.0.0-alpha.2 - added caching                              - 2022-05-13 wer
 * - 1.0.0-alpha.0 - Initial version.                           - 2018-06-01 wer
 */
class ContentView implements ViewInterface
{
    use ConfigViewTraits;

    /** @var ContentComplexModel $o_model */
    private ContentComplexModel $o_model;

    /**
     * ContentView constructor.
     *
     * @param Di $o_di
     * @throws ViewException
     */
    public function __construct(Di $o_di)
    {
        $this->setupView($o_di);
        try {
            $this->o_model = new ContentComplexModel($o_di);
        }
        catch (ModelException $e) {
            $message = 'Unable to create the ContentComplexModel instance';
            $err_no  = ExceptionHelper::getCodeTextView('view object');
            throw new ViewException($message, $err_no, $e);
        }
    }

    /**
     * Main method required by interface.
     * Returns the list of content records in a nice view.
     *
     * @param array $a_message
     * @return string
     */
    public function render(array $a_message = []): string
    {
        $a_records = [];
        $cache_key = 'content.get.all';
        if (USE_CACHE) {
            $records_json = $this->o_cache->get($cache_key);
            $a_records = json_decode($records_json, true);
        }
        if (empty($a_records)) {
            try {
                $a_records = $this->o_model->readAllCurrent();
            }
            catch (ModelException $e) {
                $message = 'Unable to read the current content records.';
                if (DEVELOPER_MODE) {
                    $message .= ' -- ' . $e->getMessage();
                }
                $a_message = ViewHelper::errorMessage($message);
            }
            if (USE_CACHE) {
                try {
                    $records_json = json_encode($a_records, JSON_THROW_ON_ERROR);
                    $this->o_cache->set($cache_key, $records_json);
                }
                catch (JsonException|CacheException) {
                    // do nothing
                }
            }
        }

        $a_twig_values = $this->createDefaultTwigValues($a_message);
        $a_twig_values['a_content_list'] = $a_records;
        $a_twig_values['content_type'] = 'list';
        $a_twig_values['cbx_vcs'] = [
            'id'          => 'content_vcs',
            'name'        => 'content_vcs',
            'value'       => CONTENT_VCS,
            'label'       => 'Use Versions',
            'other_stuph' => ' onchange="updateContentVCS()"',
            'checked'     => CONTENT_VCS ? ' checked' : ''
        ];
        $tpl = $this->createTplString($a_twig_values);
        return $this->renderIt($tpl, $a_twig_values);
    }

    /**
     * Returns a list of all versions of content for a page/block.
     *
     * @param int $pbm_id Required
     * @return string
     */
    public function renderAllVersions(int $pbm_id = -1): string
    {
        if ($pbm_id < 1) {
            $message = 'Missing required value for the page/block combo.';
            return $this->render(ViewHelper::errorMessage($message));
        }
        $a_message = [];
        $a_results = [];
        $cache_key = 'content.get.all.versions.for.pbm.' . $pbm_id;
        if (USE_CACHE) {
            $values = $this->o_cache->get($cache_key);
            $a_results = json_decode($values, true);
        }
        if (empty($a_results)) {
            try {
                $a_search_for = ['pbm_id' => $pbm_id, 'order_by' => 'DESC'];
                $a_results = $this->o_model->readAllByPage($a_search_for);
                if (USE_CACHE) {
                    try {
                        $values = json_encode($a_results, JSON_THROW_ON_ERROR);
                        $this->o_cache->set($cache_key, $values);
                    }
                    catch (JsonException|CacheException) {
                        // do nothing for now
                    }
                }
            }
            catch (ModelException) {
                $message = 'A problem occurred retreiving a list of all versions for the page';
                $a_message = ViewHelper::errorMessage($message);
            }
        }
        $a_twig_values                   = $this->createDefaultTwigValues($a_message);
        $a_twig_values['content_type']   = 'versions';
        $a_twig_values['a_content_list'] = $a_results;
        $a_twig_values['c_pbm_id']       = $pbm_id;
        $tpl = $this->createTplString($a_twig_values);
        return $this->renderIt($tpl, $a_twig_values);
    }

    public function renderDetails():string
    {
        $a_post = $this->o_router->getPost();
        $c_id = $a_post['c_id'];
        $cache_key = 'content.get.by.id.' . $c_id;
        if (USE_CACHE) {
            $value = $this->o_cache->get($cache_key);
            $a_results = json_decode($value, true);
        }
        if (empty($a_results)) {
            try {
                $a_results = $this->o_model->readByContentId($c_id);
            }
            catch (ModelException) {
                $message = 'Could not get the details for the record.';
                $a_message = ViewHelper::errorMessage($message);
                return $this->render($a_message);
            }
            if (USE_CACHE) {
                try {
                    $value = json_encode($a_results, JSON_THROW_ON_ERROR);
                    $this->o_cache->set($cache_key, $value);
                }
                catch (JsonException|CacheException) {
                    // do nothing for now but needed
                }
            }
        }
        $a_results['content_type'] = match ($a_results['c_type']) {
            'text'  => 'Text',
            'html'  => 'HTML',
            'mde'   => 'Markdown Extra',
            'xml'   => 'XML',
            'raw'   => 'Raw',
            default => 'Markdown',
        };
        $a_twig_values                 = $this->createDefaultTwigValues();
        $a_twig_values['a_record']     = $a_results;
        $a_twig_values['content_type'] = 'ro_details';
        $tpl = $this->createTplString($a_twig_values);
        return $this->renderIt($tpl, $a_twig_values);

    }

    /**
     * Renders the form to add/update/delete a content record.
     *
     * @param string $action
     * @return string
     */
    public function renderForm(string $action = 'new'): string
    {
        $a_post = $this->o_router->getPost();
        $a_record        = [
            'c_id'            => '',
            'c_content'       => '',
            'c_short_content' => '',
            'c_version'       => '',
            'c_featured'      => 'false',
            'c_type'          => 'md',
            'c_type_select'   => [],
            'pbm_id'          => '',
            'pbm_select'      => [],
            'featured_cbx'    => []
        ];
        $a_c_type_options = [];
        $a_type_options = [
            'text' => 'Text',
            'html' => 'HTML',
            'md'   => 'Markdown',
            'mde'  => 'Markdown Extra',
            'xml'  => 'XML',
            'raw'  => 'Raw'
        ];
        foreach ($a_type_options as $value => $label) {
            $a_c_type_options[] = [
                'value'       => $value,
                'other_stuph' => '',
                'label'       => $label
            ];
        }
        $a_message    = [];
        $a_pbm_select = [];
        switch ($action) {
            case 'by_pbm_id':
                $c_pbm_id = $a_post['c_pbm_id'];
                $cache_key = 'content.get.by.pbm_id.' . $c_pbm_id;
                if (USE_CACHE) {
                    $value = $this->o_cache->get($cache_key);
                    $a_record = json_decode($value, true);
                }
                if (empty($a_record)) {
                    try {
                        $a_records = $this->o_model->readCurrent(['pbm_id' => $c_pbm_id]);
                        if (!empty($a_records[0])) {
                            $a_record = $a_records[0];
                            if (USE_CACHE) {
                                try {
                                    $value = json_encode($a_record, JSON_THROW_ON_ERROR);
                                    $this->o_cache->set($cache_key, $value);
                                }
                                catch (JsonException|CacheException) {
                                    // do nothing for now
                                }
                            }
                        }
                        else {
                            $a_message = ViewHelper::errorMessage('Unable to read the details for the content record.');
                        }
                    }
                    catch (ModelException) {
                        $a_message = ViewHelper::errorMessage('Unable to read the details for the content record.');
                    }
                }
                break;
            case 'by_c_id':
                $err_msg = 'Unable to read the details for the content record.';
                $content_id = -1;
                if (!empty($a_post['c_id'])) {
                    $content_id = $a_post['c_id'];
                }
                elseif (!empty($a_post['content']['c_id'])) {
                    $content_id = $a_post['content']['c_id'];
                }
                else {
                    $a_message = ViewHelper::errorMessage($err_msg);
                }
                $cache_key = 'content.get.by.content_id.' . $content_id;
                if (USE_CACHE) {
                    $value = $this->o_cache->get($cache_key);
                    $a_record = json_decode($value, true);
                }
                if (empty($a_message) && empty($a_record)) {
                    try {
                        $a_record = $this->o_model->readByContentId($content_id);
                        if (USE_CACHE) {
                            try {
                                $value = json_encode($a_record, JSON_THROW_ON_ERROR);
                                $this->o_cache->set($cache_key, $value);
                            }
                            catch (JsonException|CacheException) {
                                // do nothing for now
                            }
                        }
                    }
                    catch (ModelException) {
                        $a_message = ViewHelper::errorMessage($err_msg);
                    }
                }
                break;
            case 'new':
            default:
                $o_pbm = new PageBlocksMapModel($this->o_db);
                $a_pbm_options = [[
                                      'value'       => '',
                                      'other_stuph' => ' selected',
                                      'label'       => '-Select Page/Block for Content-'
                                  ]];
                $cache_key = 'pbm.get.without.content';
                $a_pbms = [];
                if (USE_CACHE) {
                    $value = $this->o_cache->get($cache_key);
                    $a_pbms = json_decode($value, true);
                }
                if (empty($a_pbms)) {
                    try {
                        $a_pbms = $o_pbm->readPbmWithoutContent();
                    }
                    catch (ModelException) {
                        $a_message = ViewHelper::errorMessage('Unable to determine the pages that exist.');
                    }
                }
                foreach ($a_pbms as $a_pbm) {
                    $a_pbm_options[] = [
                        'value'       => $a_pbm['pbm_id'],
                        'other_stuph' => '',
                        'label'       => $a_pbm['page_title'] . '/' . $a_pbm['b_name']
                    ];
                }
                $a_pbm_select = [
                    'id'          => 'content[c_pbm_id]',
                        'name'        => 'content[c_pbm_id]',
                        'class'       => 'form-control colorful',
                        'other_stuph' => '',
                        'label_class' => 'form-label bold',
                        'label_text'  => 'Available Page/Block Combinations without Content',
                        'options'     => $a_pbm_options
                    ];
            // end default/new
        }
        if (!empty($a_message)) {
            return $this->render($a_message);
        }
        $a_featured = [
            'id'      => 'content[c_featured]',
            'name'    => 'content[c_featured]',
            'label'   => 'Featured',
            'value'   => 'true',
            'checked' => $a_record['c_featured'] === 'true' ? ' checked' : ''
        ];
        foreach ($a_c_type_options as $key => $a_option) {
            $a_c_type_options[$key]['other_stuph'] = '';
            if ($a_record['c_type'] === $a_option['value']) {
                $a_c_type_options[$key]['other_stuph'] = ' selected';
            }
        }
        $a_c_type_select = [
            'id'          => 'content[c_type]',
            'name'        => 'content[c_type]',
            'class'       => 'form-control colorful',
            'other_stuph' => '',
            'label_class' => 'form-label bold',
            'label_text'  => 'Content Type',
            'options'     => $a_c_type_options
        ];
        $a_record['pbm_select']        = $a_pbm_select;
        $a_record['c_type_select']     = $a_c_type_select;
        $a_record['featured_cbx']      = $a_featured;
        $a_twig_values                 = $this->createDefaultTwigValues($a_message);
        $a_twig_values['a_record']     = $a_record;
        $a_twig_values['content_type'] = 'details';
        $a_twig_values['has_versions'] = CONTENT_VCS ? 'true' : 'false';
        $tpl = $this->createTplString($a_twig_values);
        return $this->renderIt($tpl, $a_twig_values);
    }
}
