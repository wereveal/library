<?php
/**
 * Class PageView
 * @package Ritc_Library
 */
namespace Ritc\Library\Views;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Exceptions\ViewException;
use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Helper\FormHelper;
use Ritc\Library\Models\BlocksModel;
use Ritc\Library\Models\PageBlocksMapModel;
use Ritc\Library\Models\PageComplexModel;
use Ritc\Library\Helper\Arrays;
use Ritc\Library\Models\PageModel;
use Ritc\Library\Models\UrlsModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigViewTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * View for the Page Admin page.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v2.1.0
 * @date    2017-06-20 11:46:05
 * @change_log
 * - v2.1.0   - method name changed elsewhere changed here                              - 2017-06-20 wer
 *              ModelException handling added
 * - v2.0.0   - name refactoring                                                        - 2017-05-14 wer
 * - v1.4.0   - Lot of fixes due to the addition of URLs                                - 2016-04-13 wer
 * - v1.3.0   - Refactored the tpls to implement LIB_TWIG_PREFIX pushed changes here    - 2016-04-11 wer
 * - v1.2.0   - Bug fix for implementation of LIB_TWIG_PREFIX                           - 2016-04-10 wer
 * - v1.1.0   - Implement LIB_TWIG_PREFIX                                               - 12/12/2015 wer
 * - v1.0.0   - take out of beta                                                        - 11/27/2015 wer
 * - v1.0.0β1 - Initial version                                                         - 10/30/2015 wer
 */
class PageView
{
    use LogitTraits, ConfigViewTraits;

    /** @var \Ritc\Library\Models\PageComplexModel */
    private $o_model;

    /**
     * PageView constructor.
     *
     * @param \Ritc\Library\Services\Di $o_di
     * @throws ViewException
     */
    public function __construct(Di $o_di)
    {
        $this->setupView($o_di);
        try {
            $this->o_model = new PageComplexModel($this->o_di);
            $this->o_model->setupElog($o_di);
        }
        catch (ModelException $e) {
            $message  = 'Unable to create an instance of PageComplexModel.';
            $err_code = ExceptionHelper::getCodeNumberModel('instance');
            throw new ViewException($message, $err_code);
        }
        $this->setupElog($o_di);
    }

    /**
     * Returns a form to enter page data into db.
     *
     * @param string $new_or_modify Defaults to new
     * @param int    $page_id
     * @return string
     */
    public function renderForm($new_or_modify = 'new_page', $page_id = -1):string
    {
        $meth = __METHOD__ . '.';
        /**
         * Because I couldn't remember, documenting this here.
         * Coming to this method, there are two possible form actions, 'new_page' or 'modify_page'.
         * This triggers two things: specifies the action to be use on the page form,
         * and if we do a lookup of the page values that we are modifying.
         */
        $o_urls = new UrlsModel($this->o_db);
        $o_urls->setupElog($this->o_di);
        try {
            $a_urls = $o_urls->read();
        }
        catch (ModelException $e) {
            $a_urls = [];
        }
        $o_page = new PageModel($this->o_db);
        $o_page->setupElog($this->o_di);
        try {
            $a_page_list = $o_page->read();
        }
        catch (ModelException $e) {
            $a_page_list = [];
        }
        $o_blocks = new BlocksModel($this->o_db);
        $o_blocks->setupElog($this->o_di);
        try {
            $a_block_list = [];
            $a_blocks = $o_blocks->readActive();
            foreach ($a_blocks as $a_block) {
                $a_block_list[] = [
                    'id'      => 'blocks[' . $a_block['b_id'] . ']',
                    'name'    => 'blocks[' . $a_block['b_id'] . ']',
                    'value'   => 'true',
                    'checked' => '',
                    'label'   => $a_block['b_name']
                ];
            }
        }
        catch (ModelException $e) {
            $a_block_list = [];
        }
        $a_options = [
            [
                'value'       => '0',
                'label'       => '--Select URL--',
                'other_stuph' => ' selected'
            ]
        ];
        foreach ($a_urls as $key => $a_url) {
            $results = Arrays::inAssocArrayRecursive('url_id', $a_url['url_id'], $a_page_list);
            if (!$results) {
                $a_options[] = [
                    'value'       => $a_url['url_id'],
                    'label'       => $a_url['url_text'],
                    'other_stuph' => ''
                ];
            }
        }
        $a_select = [
            'id'          => 'page[url_id]',
            'name'        => 'page[url_id]',
            'class'       => 'form-control',
            'other_stuff' => '',
            'options'     => $a_options
        ];
        $a_page = [
            'page_id'          => '',
            'url_id'           => '',
            'url_text'         => '',
            'page_title'       => '',
            'page_description' => '',
            'page_base_url'    => '/',
            'page_type'        => 'text/html',
            'page_lang'        => 'en',
            'page_charset'     => 'utf8',
            'page_immutable'   => 'false',
        ];
        $a_twig_values = $this->createDefaultTwigValues();
        $a_twig_values['url_select'] = $a_select;
        $a_twig_values['action']     = $new_or_modify === 'modify_page' ? 'update' : 'save';
        $a_twig_values['a_page']     = $a_page;
        $a_twig_values['which_page'] = 'form';
        ### If we are trying to modify an existing page, grab its data and shove it into the form ###
        if ($new_or_modify === 'modify_page') {
            try {
                $a_pages = $this->o_model->readPageValues(
                    ['page_id' => $page_id]
                );
            }
            catch (ModelException $e) {
                $a_pages = [];
            }
            $o_pbm = new PageBlocksMapModel($this->o_db);
            $o_pbm->setupElog($this->o_di);
            try {
                $a_pbm = $o_pbm->readByPageId($page_id);
            }
            catch (ModelException $e) {
                $a_pbm = [];
            }
            foreach ($a_pbm as $a_record) {
                foreach ($a_block_list as $key => $a_block) {
                    if ($a_block['b_id'] === 'blocks[' . $a_record['pbm_block_id'] . ']') {
                        $a_block_list[$key]['checked'] = ' checked';
                    }
                }
            }
            if (!empty($a_pages[0])) {
                $a_page = $a_pages[0];
                $a_twig_values['a_page'] = $a_page;

                $label = $a_page['url_host'] === 'self'
                    ? $a_page['url_text']
                    : $a_page['url_host'] . $a_page['url_text'];

                $a_twig_values['url_select']['options'][] = [
                    'value'       => $a_page['url_id'],
                    'label'       => $label,
                    'other_stuph' => ' selected'
                ];
                $a_twig_values['url_select']['options'][0]['other_stuph'] = ''; // this should be the default --Select--
            }
            else {
                $this->logIt('Could not get the page values!', LOG_OFF, $meth . __LINE__);
            }
        }
        $a_twig_values['a_blocks'] = $a_block_list;
        $tpl = $this->createTplString($a_twig_values);
        return $this->renderIt($tpl, $a_twig_values);
    }

    /**
     * Returns the list of pages in html.
     *
     * @param array $a_message Optional
     * @return string
     */
    public function renderList(array $a_message = []):string
    {
        $meth = __METHOD__ . '.';
        $a_search_for = [];
        $a_search_params = ['order_by' => 'page_immutable DESC, url_text ASC'];
        try {
            $a_pages = $this->o_model->readPageValues($a_search_for, $a_search_params);
        }
        catch (ModelException $e) {
            $a_pages = [];
        }
        foreach($a_pages as $key => $a_page) {
            if ($a_page['url_host'] === 'self') {
                $a_pages[$key]['page_url'] = $a_page['url_text'];
            }
            else {
                $a_pages[$key]['page_url'] = $a_page['url_scheme'] . '://' . $a_page['url_host'] .  $a_page['url_text'];
            }
        }
        $a_twig_values = $this->createDefaultTwigValues($a_message);
        $a_twig_values['a_pages']    = $a_pages;
        $a_twig_values['a_message']  = $a_message;
        $a_twig_values['which_page'] = 'list';
        $a_btn_form = [
            'form_action' => '/manager/config/pages/',
            'btn_value'   => 'new_page',
            'btn_label'   => 'New Page',
            'btn_size'    => 'btn-sm'
        ];
        $a_twig_values['new_btn'] = FormHelper::singleBtnForm($a_btn_form);
        $tpl = $this->createTplString($a_twig_values);
        $log_message = 'Twig Values ' . var_export($a_twig_values, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
        $this->logIt('tpl: ' . $tpl, LOG_OFF, $meth . __LINE__);
        return $this->renderIt($tpl, $a_twig_values);
    }

    /**
     * Returns HTML verify form to delete.
     *
     * @return string
     */
    public function renderVerify():string
    {
        $meth = __METHOD__ . '.';
        $a_post_values = $this->o_router->getPost();
        $a_page = $a_post_values['page'];
          $log_message = 'Post Values ' . var_export($a_post_values, TRUE);
          $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        $a_twig_values = $this->createDefaultTwigValues();
        $a_values = array(
            'what'         => 'Page ',
            'name'         => $a_page['page_title'],
            'where'        => 'pages',
            'btn_value'    => 'Page',
            'hidden_name'  => 'page_id',
            'hidden_value' => $a_page['page_id'],
        );
        $a_twig_values = array_merge($a_twig_values, $a_values);
        $message = 'Twig Values: ' . var_export($a_twig_values, TRUE);
        $this->logIt($message, LOG_OFF, $meth . __LINE__);
        $tpl = $this->createTplString($a_twig_values);
        return $this->renderIt($tpl, $a_twig_values);
    }
}
