<?php
/**
 * Class ContentView.
 *
 * @package Ritc_Library
 */

namespace Ritc\Library\Views;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Exceptions\ViewException;
use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ViewInterface;
use Ritc\Library\Models\ContentComplexModel;
use Ritc\Library\Models\PageBlocksMapModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigViewTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Manager for Content View.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.0
 * @date    2018-06-01 11:44:46
 * @change_log
 * - v1.0.0-alpha.0 - Initial version.                                    - 2018-06-01 wer
 */
class ContentView implements ViewInterface
{
    use LogitTraits, ConfigViewTraits;

    /** @var ContentComplexModel $o_model */
    private $o_model;

    /**
     * ContentView constructor.
     *
     * @param \Ritc\Library\Services\Di $o_di
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
        $this->setupElog($o_di);
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
          $log_message = 'Records:  ' . var_export($a_records, true);
          $this->logIt($log_message, LOG_OFF, __METHOD__);
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
    public function renderAllVersions($pbm_id = -1): string
    {
        if ($pbm_id < 1) {
            $message = 'Missing required value for the page/block combo.';
            return $this->render(ViewHelper::errorMessage($message));
        }
        $a_message = [];
        $a_results = [];
        try {
            $a_search_for = ['pbm_id' => $pbm_id, 'order_by' => 'DESC'];
            $a_results = $this->o_model->readAllByPage($a_search_for);
        }
        catch (ModelException $e) {
            $message = 'A problem occurred retreiving a list of all versions for the page';
            $a_message = ViewHelper::errorMessage($message);
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
        try {
            $a_results = $this->o_model->readByContentId($c_id);
        }
        catch (ModelException $e) {
            $message = 'Could not get the details for the record.';
            $a_message = ViewHelper::errorMessage($message);
            return $this->render($a_message);
        }
        switch ($a_results['c_type']) {
            case 'text':
                $a_results['content_type'] = 'Text';
                break;
            case 'html':
                $a_results['content_type'] = 'HTML';
                break;
            case 'mde':
                $a_results['content_type'] = 'Markdown Extra';
                break;
            case 'xml':
                $a_results['content_type'] = 'XML';
                break;
            case 'raw':
                $a_results['content_type'] = 'Raw';
                break;
            case 'md':
            default:
                $a_results['content_type'] = 'Markdown';
        }
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
    public function renderForm($action = 'new'): string
    {
        $meth = __METHOD__ . '.';
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
                try {
                    $a_records = $this->o_model->readCurrent(['pbm_id' => $c_pbm_id]);
                    if (!empty($a_records[0])) {
                        $a_record = $a_records[0];
                        $a_pbm_select = [];
                    }
                    else {
                        $a_message = ViewHelper::errorMessage('Unable to read the details for the content record.');
                    }
                }
                catch (ModelException $e) {
                    $a_message = ViewHelper::errorMessage('Unable to read the details for the content record.');
                }
                break;
            case 'by_c_id':
                $err_msg = 'Unable to read the details for the content record.';
                if (!empty($a_post['c_id'])) {
                    $content_id = $a_post['c_id'];
                }
                elseif (!empty($a_post['content']['c_id'])) {
                    $content_id = $a_post['content']['c_id'];
                }
                else {
                    $a_message = ViewHelper::errorMessage($err_msg);
                }
                if (empty($a_message)) {
                    try {
                        $a_record = $this->o_model->readByContentId($content_id);
                        $a_pbm_select = [];
                    }
                    catch (ModelException $e) {
                        $a_message = ViewHelper::errorMessage($err_msg);
                    }
                }
                break;
            case 'new':
            default:
                $o_pbm = new PageBlocksMapModel($this->o_db);
                $o_pbm->setupElog($this->o_di);
                $a_pbm_options = [[
                                      'value'       => '',
                                      'other_stuph' => ' selected',
                                      'label'       => '-Select Page/Block for Content-'
                                  ]];
                try {
                    $a_pbms = $o_pbm->readPbmWithoutContent();
                    foreach ($a_pbms as $a_pbm) {
                        $a_pbm_options[] = [
                            'value'       => $a_pbm['pbm_id'],
                            'other_stuph' => '',
                            'label'       => $a_pbm['page_title'] . '/' . $a_pbm['b_name']
                        ];
                    }
                }
                catch (ModelException $e) {
                    $a_message = ViewHelper::errorMessage('Unable to determine the pages that exist.');
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
          $log_message = 'twig values ' . var_export($a_twig_values, true);
          $this->logIt($log_message, LOG_ON, $meth . __LINE__);
          $this->logIt('TPL: ' . $tpl, LOG_ON, $meth . __LINE__);
        return $this->renderIt($tpl, $a_twig_values);
    }

}
