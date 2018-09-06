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
use Ritc\Library\Models\BlocksModel;
use Ritc\Library\Models\ContentComplexModel;
use Ritc\Library\Models\PageModel;
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
        $a_twig_values['is_list'] = true;
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
        $a_record        = [
            'c_id'            => '',
            'c_content'       => '',
            'c_short_content' => '',
            'c_version'       => '',
            'c_featured'      => 'false',
            'c_shared'        => 'false',
            'page_id'         => '',
            'page_select'     => [],
            'b_id'            => '',
            'blocks_select'   => [],
            'featured_cbx'    => [],
            'shared_cbx'      => []
        ];
        $a_message       = [];
        $a_page_options  = [];
        $a_block_options = [];
        if ($action !== 'new') {
            $a_post     = $this->o_router->getPost();
            $content_id = $a_post['c_id'];
            try {
                $a_record = $this->o_model->readByContentId($content_id);
            }
            catch (ModelException $e) {
                $a_message = ViewHelper::errorMessage('Unable to read the details for the content record.');
            }
        }
        $o_page   = new PageModel($this->o_db);
        $o_blocks = new BlocksModel($this->o_db);
        $o_page->setupElog($this->o_di);
        $o_blocks->setupElog($this->o_di);
        try {
            $a_pages   = $o_page->read();
            $pages_pin = $o_page->getPrimaryIndexName();
            foreach ($a_pages as $a_page) {
                $other_stuph      = $a_record[$pages_pin] === $a_page[$pages_pin] ? ' selected' : '';
                $a_page_options[] = [
                    'value'       => $a_page[$pages_pin],
                    'other_stuph' => $other_stuph,
                    'label'       => $a_page['page_title']
                ];
            }
            try {
                $a_blocks   = $o_blocks->read();
                $blocks_pin = $o_blocks->getPrimaryIndexName();
                foreach ($a_blocks as $a_block) {
                    $other_stuph       = $a_record[$blocks_pin] === $a_block[$blocks_pin] ? ' selected' : '';
                    $a_block_options[] = [
                        'value'       => $a_block[$blocks_pin],
                        'other_stuph' => $other_stuph,
                        'label'       => $a_block['b_name']
                    ];
                }
            }
            catch (ModelException $e) {
                $a_message = ViewHelper::errorMessage('Unable to determine the blocks that exist.');
            }
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::errorMessage('Unable to determine the pages that exist.');
        }
        $a_pages                    = [
            'id'          => 'content[page_id]',
            'name'        => 'content[page_id]',
            'class'       => 'form-control colorful',
            'other_stuph' => '',
            'label_class' => 'form-label bold',
            'label_text'  => 'Page',
            'options'     => $a_page_options
        ];
        $a_blocks                   = [
            'id'          => 'content[b_id]',
            'name'        => 'content[b_id]',
            'class'       => 'form-control colorful',
            'other_stuph' => '',
            'label_class' => 'form-label bold',
            'label_text'  => 'Block',
            'options'     => $a_block_options
        ];
        $a_featured                 = [
            'id'      => 'content[c_featured]',
            'name'    => 'content[c_featured]',
            'label'   => 'Featured',
            'value'   => 'true',
            'checked' => $a_record['c_featured'] === 'true' ? ' checked' : ''
        ];
        $a_shared                   = [
            'id'      => 'content[c_shared]',
            'name'    => 'content[c_shared]',
            'label'   => 'Shared',
            'value'   => 'true',
            'checked' => $a_record['c_shared'] === 'true' ? ' checked' : ''
        ];
        $a_record['page_select']    = $a_pages;
        $a_record['blocks_select']  = $a_blocks;
        $a_record['featured_cbx']   = $a_featured;
        $a_record['shared_cbx']     = $a_shared;
        $a_twig_values              = $this->createDefaultTwigValues($a_message);
        $a_twig_values['a_content'] = $a_record;
        $a_twig_values['is_list']   = false;
        $tpl                        = $this->createTplString($a_twig_values);
        $log_message = 'twig values ' . var_export($a_twig_values, true);
        $this->logIt($log_message, LOG_ON, $meth . __LINE__);
        $this->logIt('TPL: ' . $tpl, LOG_ON, $meth . __LINE__);
        return $this->renderIt($tpl, $a_twig_values);
    }
}
