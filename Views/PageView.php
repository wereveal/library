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
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Models\BlocksModel;
use Ritc\Library\Models\NavgroupsModel;
use Ritc\Library\Models\PageBlocksMapModel;
use Ritc\Library\Models\PageComplexModel;
use Ritc\Library\Helper\Arrays;
use Ritc\Library\Models\PageModel;
use Ritc\Library\Models\TwigPrefixModel;
use Ritc\Library\Models\TwigTemplatesModel;
use Ritc\Library\Models\UrlsModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigViewTraits;

/**
 * View for the Page Admin page.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 3.0.0
 * @date    2021-12-01 13:20:06
 * @change_log
 * - 3.0.0   - updated for php 8 standards                                              - 2021-12-01 wer
 * - 2.2.0   - renderForm updated to match changes in data.                             - 2018-12-29 wer
 * - 2.1.0   - method name changed elsewhere changed here                               - 2017-06-20 wer
 *              ModelException handling added
 * - 2.0.0   - name refactoring                                                         - 2017-05-14 wer
 * - 1.4.0   - Lot of fixes due to the addition of URLs                                 - 2016-04-13 wer
 * - 1.3.0   - Refactored the tpls to implement LIB_TWIG_PREFIX pushed changes here     - 2016-04-11 wer
 * - 1.2.0   - Bug fix for implementation of LIB_TWIG_PREFIX                            - 2016-04-10 wer
 * - 1.1.0   - Implement LIB_TWIG_PREFIX                                                - 12/12/2015 wer
 * - 1.0.0   - take out of beta                                                         - 11/27/2015 wer
 * - 1.0.0β1 - Initial version                                                          - 10/30/2015 wer
 */
class PageView
{
    use ConfigViewTraits;

    /** @var PageComplexModel */
    private PageComplexModel $o_model;

    /**
     * PageView constructor.
     *
     * @param Di $o_di
     * @throws ViewException
     */
    public function __construct(Di $o_di)
    {
        $this->setupView($o_di);
        try {
            $this->o_model = new PageComplexModel($this->o_di);
        }
        catch (ModelException) {
            $message  = 'Unable to create an instance of PageComplexModel.';
            $err_code = ExceptionHelper::getCodeNumberModel('instance');
            throw new ViewException($message, $err_code);
        }
    }

    /**
     * Returns a form to enter page data into db.
     *
     * @param string $new_or_modify Defaults to new
     * @param int    $page_id
     * @return string
     */
    public function renderForm(string $new_or_modify = 'new_page', int $page_id = -1):string
    {
        /**
         * Because I couldn't remember, documenting this here.
         * Coming to this method, there are two possible form actions, 'new_page' or 'modify_page'.
         * This triggers two things: specifies the action to be use on the page form,
         * and if we do a lookup of the page values that we are modifying.
         */
        $o_urls = new UrlsModel($this->o_db);
        try {
            $a_urls = $o_urls->read();
        }
        catch (ModelException) {
            $a_urls = [];
        }
        $o_page = new PageModel($this->o_db);
        try {
            $a_page_list = $o_page->read();
        }
        catch (ModelException) {
            $a_page_list = [];
        }
        $o_ng = new NavgroupsModel($this->o_db);
        try {
            $a_ng_list = $o_ng->read(['ng_active' => 'true']);
        }
        catch (ModelException) {
            $a_ng_list = [];
        }
        $a_ng_options = [[
            'value' => '',
            'label' => '-Select Navgroup-'
        ]];
        foreach ($a_ng_list as $a_ng) {
            $a_ng_options[] = [
                'value' => $a_ng['ng_id'],
                'label' => $a_ng['ng_name']
            ];
        }
        $o_twig_prefix = new TwigPrefixModel($this->o_db);
        try {
            $a_prefixes = $o_twig_prefix->read();
        }
        catch (ModelException) {
            $a_prefixes = [];
        }
        $a_options = [[
            'value' => '',
            'label' => '-Select Twig Prefix-'
        ]];
        foreach ($a_prefixes as $a_prefix) {
            $a_options[] = [
                'value' => $a_prefix['tp_id'],
                'label' => $a_prefix['tp_prefix']
            ];
        }
        $a_prefix_select = [
            'id'          => 'tp_id',
            'label_class' => 'form-label bold',
            'label_text'  => 'Twig Prefix',
            'label_extra' => '',
            'name'        => 'tp_id',
            'class'       => 'form-control',
            'other_stuph' => ' onchange="switchPageDirsForPrefix(this)"',
            'options'     => $a_options
        ];
        $a_dir_select = [
            'id'          => 'td_id',
            'label_class' => 'form-label bold',
            'label_text'  => 'Twig Directory',
            'label_extra' => '',
            'name'        => 'td_id',
            'class'       => 'form-control',
            'other_stuph' => ' onchange="switchTplForDir(this)"',
            'options'     => [[
                'value' => '',
                'label' => '-Select the twig prefix first-'
            ]]
        ];
        $a_tpl_select = [
            'id'          => 'tpl_id',
            'label_class' => 'form-label bold',
            'label_text'  => 'Page Template',
            'label_extra' => ' <span class="text-danger">*</span>',
            'name'        => 'page[tpl_id]',
            'class'       => 'form-control',
            'other_stuph' => ' required',
            'options'     => [[
                'value' => '',
                'label' => '-Select the twig prefix first-'
            ]]
        ];
        $a_ng_select = [
            'id'          => 'ng_id',
            'label_class' => 'form-label bold',
            'label_text'  => 'Navgroup',
            'label_extra' => ' <span class="text-danger">*</span>',
            'name'        => 'page[ng_id]',
            'class'       => 'form-control',
            'other_stuph' => ' required',
            'options'     => $a_ng_options
        ];
        $a_options = [
            [
                'value'       => '',
                'label'       => '--Select URL--',
                'other_stuph' => ' selected'
            ]
        ];
        foreach ($a_urls as $a_url) {
            $results = Arrays::inAssocArrayRecursive('url_id', $a_url['url_id'], $a_page_list);
            if (!$results) {
                $a_options[] = [
                    'value'       => $a_url['url_id'],
                    'label'       => $a_url['url_text'],
                    'other_stuph' => ''
                ];
            }
        }
        $a_url_select = [
            'id'          => 'page[url_id]',
            'name'        => 'page[url_id]',
            'class'       => 'form-control',
            'label_text'  => 'Page Url: ',
            'label_class' => 'form-label bold',
            'label_extra' => ' <span class="text-danger">*</span>',
            'other_stuph' => ' required',
            'options'     => $a_options
        ];
        $a_immutable_cbx = [
            'id'      => 'page[page_immutable]',
            'name'    => 'page[page_immutable]',
            'label'   => 'Immutable',
            'value'   => 'false',
            'checked' => ''
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
            'page_charset'     => 'utf-8',
            'page_immutable'   => 'false',
            'page_changefreq'  => 'yearly',
            'page_priority'    => '0.5'
        ];
        $a_changefreq = [
            'always',
            'hourly',
            'daily',
            'weekly',
            'monthly',
            'yearly',
            'never'
        ];
        $a_changefreq_options = [
            [
                'value'       => '',
                'label'       => '-Select Frequency-',
                'other_stuph' => ''
            ]
        ];
        $a_priority_options = [
            [
                'value'       => '',
                'label'       => '-Select Priority-',
                'other_stuph' => ''
            ]
        ];
        for ($x = 0.0; $x <= 1.0; $x += 0.1) {
            $other_stuph = $x === 0.5 ? ' selected' : '';
            $option_value = (string) $x;
            if ($option_value === '0') {
                $option_value = '0.0';
            }
            if ($option_value === '1') {
                $option_value = '1.0';
            }
            $a_priority_options[] = [
                'value'       => $option_value,
                'label'       => $option_value,
                'other_stuph' => $other_stuph
            ];
        }
        foreach ($a_changefreq as $changefreq) {
            $other_stuph = $changefreq === 'yearly' ? ' selected' : '';
            $a_changefreq_options[] = [
                'value'       => $changefreq,
                'label'       => $changefreq,
                'other_stuph' => $other_stuph
            ];
        }
        $a_twig_values = $this->createDefaultTwigValues();
        $a_twig_values['url_select'] = $a_url_select;
        $a_twig_values['action']     = $new_or_modify === 'modify_page' ? 'update' : 'save';
        $a_twig_values['which_page'] = 'form';
        ### If we are trying to modify an existing page, grab its data and shove it into the form ###
        if ($new_or_modify === 'modify_page') {
            try {
                $a_pages = $this->o_model->readPageValues(
                    ['page_id' => $page_id]
                );
            }
            catch (ModelException) {
                $a_pages = [];
            }
            if (!empty($a_pages[0])) {
                $a_page = $a_pages[0];
                $label = $a_page['url_host'] === 'self'
                    ? $a_page['url_text']
                    : $a_page['url_host'] . $a_page['url_text'];

                $a_twig_values['url_select']['options'][] = [
                    'value'       => $a_page['url_id'],
                    'label'       => $label,
                    'other_stuph' => ' selected'
                ];
                foreach ($a_ng_select['options'] as $key => $a_option) {
                    if ($a_option['value'] === $a_page['ng_id']) {
                        $a_ng_select['options'][$key]['other_stuph'] = ' selected';
                    }
                }
                if ($a_page['page_immutable'] === 'true') {
                    $a_immutable_cbx['value'] = 'true';
                    $a_immutable_cbx['checked'] = ' checked';
                }
                $a_twig_values['url_select']['options'][0]['other_stuph'] = ''; // this should be the default --Select--
                $o_tpl = new TwigTemplatesModel($this->o_db);
                try {
                    $a_tpls = $o_tpl->read(['tpl_id' => $a_page['tpl_id']]);
                    $a_tpl_select['options'] = [
                        [
                            'value'       => $a_page['tpl_id'],
                            'label'       => $a_tpls[0]['tpl_name'],
                            'other_stuph' => ' selected'
                        ],
                        [
                            'value' => 'reset',
                            'label' => '-Select a different template-'
                        ]
                    ];
                }
                catch (ModelException $e) {
                    $a_msg_values = [
                        'message' => 'Could not get the page values! ' . $e->getMessage(),
                        'type'    => 'error'
                    ];
                    $a_message = ViewHelper::fullMessage($a_msg_values);
                    $a_twig_values['a_message'] = $a_message;

                }
            }
            else {
                $a_msg_values = [
                    'message' => 'Could not get the page values!',
                    'type'    => 'error'
                ];
                $a_message = ViewHelper::fullMessage($a_msg_values);
                $a_twig_values['a_message'] = $a_message;
            }
        }
        $o_blocks = new BlocksModel($this->o_db);
        $a_block_list = [];
        try {
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
        catch (ModelException) {
            // do nothing
        }
        if (!empty($a_page['page_id'])) {
            $o_pbm = new PageBlocksMapModel($this->o_db);
            try {
                $a_pbm = $o_pbm->readByPageId($a_page['page_id']);
            }
            catch (ModelException) {
                $a_pbm = [];
            }
            foreach ($a_pbm as $a_record) {
                foreach ($a_block_list as $key => $a_block) {
                    if ($a_block['id'] === 'blocks[' . $a_record['pbm_block_id'] . ']') {
                        $a_block_list[$key]['checked'] = ' checked';
                    }
                }
            }
            foreach ($a_changefreq_options as $key => $a_changefreq_option) {
                if ($a_changefreq_option['value'] === $a_page['page_changefreq']) {
                    $a_changefreq_options[$key]['other_stuph'] = ' selected';
                }
                else {
                    $a_changefreq_options[$key]['other_stuph'] = '';
                }
            }
            foreach ($a_priority_options as $key => $a_priority_option) {
                if ($a_priority_option['value'] === $a_page['page_priority']) {
                    $a_priority_options[$key]['other_stuph'] = ' selected';
                }
                else {
                    $a_priority_options[$key]['other_stuph'] = '';
                }
            }
        }
        $a_changefreq_select = [
            'id'          => 'page[page_changefreq]',
            'name'        => 'page[page_changefreq]',
            'class'       => 'form-control',
            'label_text'  => 'Sitemap Change Frequency: ',
            'label_class' => 'form-label bold',
            'label_extra' => '',
            'other_stuph' => '',
            'options'     => $a_changefreq_options
        ];
        $a_priority_select = [
            'id'          => 'page[page_priority]',
            'name'        => 'page[page_priority]',
            'class'       => 'form-control',
            'label_text'  => 'Sitemap Priority: ',
            'label_class' => 'form-label bold',
            'label_extra' => '',
            'other_stuph' => '',
            'options'     => $a_priority_options
        ];
        $a_twig_values['a_page']             = $a_page;
        $a_twig_values['ng_select']          = $a_ng_select;
        $a_twig_values['twig_prefix_select'] = $a_prefix_select;
        $a_twig_values['twig_dir_select']    = $a_dir_select;
        $a_twig_values['twig_tpl_select']    = $a_tpl_select;
        $a_twig_values['immutable_cbx']      = $a_immutable_cbx;
        $a_twig_values['a_blocks']           = $a_block_list;
        $a_twig_values['changefreq_select']  = $a_changefreq_select;
        $a_twig_values['priority_select']    = $a_priority_select;
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
        $a_search_for = [];
        $a_search_params = ['order_by' => 'page_immutable DESC, url_text ASC'];
        try {
            $a_pages = $this->o_model->readPageValues($a_search_for, $a_search_params);
        }
        catch (ModelException) {
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
        return $this->renderIt($tpl, $a_twig_values);
    }

    /**
     * Returns HTML verify form to delete.
     *
     * @return string
     */
    public function renderVerify():string
    {
        $a_post_values = $this->o_router->getPost();
        $a_page = $a_post_values['page'];

        $a_twig_values = $this->createDefaultTwigValues();
        $a_values = [
            'what'         => 'Page ',
            'name'         => $a_page['page_title'],
            'where'        => 'pages',
            'btn_value'    => 'Page',
            'hidden_name'  => 'page_id',
            'hidden_value' => $a_page['page_id']
        ];
        $a_twig_values = array_merge($a_twig_values, $a_values);
        $tpl = $this->createTplString($a_twig_values);
        return $this->renderIt($tpl, $a_twig_values);
    }
}
