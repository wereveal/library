<?php
namespace Ritc\Library\Views;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Models\PageComplexModel;
use Ritc\Library\Helper\Arrays;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Models\PageModel;
use Ritc\Library\Models\UrlsModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Traits\ViewTraits;

/**
 * View for the Page Admin page.
 * @package RITC_Library
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v2.1.0
 * @date    2017-06-20 11:46:05
 * ## Change Log
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
    use LogitTraits, ViewTraits;

    /**
     * @var \Ritc\Library\Models\PageComplexModel
     */
    private $o_model;

    /**
     * PageView constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupView($o_di);
        $this->o_model = new PageComplexModel($this->o_di);
        if (DEVELOPER_MODE) {
            $this->o_elog = $o_di->get('elog');
            $this->o_model->setElog($this->o_elog);
        }
    }

    /**
     * Returns a form to enter page data into db.
     * @param array $a_message
     * @return string
     */
    public function renderForm(array $a_message = array())
    {
        $meth = __METHOD__ . '.';
        $a_page_values = $this->getPageValues();
        /**
         * Because I couldn't remember, documenting this here.
         * Coming to this method, there are two possible form actions, 'new_page' or 'modify_page'.
         * This triggers two things: specifies the action to be use on the page form,
         * and if we do a lookup of the page values that we are modifying.
         */
        $action = $this->o_router->getFormAction() == 'new_page'
            ? 'save'
            : 'update';
        $o_urls = new UrlsModel($this->o_db);
        try {
            $a_urls = $o_urls->read();
        }
        catch (ModelException $e) {
            $a_urls = [];
        }
        $o_page = new PageModel($this->o_db);
        try {
            $a_page_list = $o_page->read();
        }
        catch (ModelException $e) {
            $a_page_list = [];
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
            'name'         => 'page[url_id]',
            'select_class' => 'form-control',
            'other_stuff'  => '',
            'options'      => $a_options
        ];
        $a_values = [
            'adm_lvl' => $this->adm_level,
            'a_message' => $a_message,
            'a_page'    => [
                'page_id'          => '',
                'url_id'           => '',
                'page_title'       => '',
                'page_description' => '',
                'page_base_url'    => '/',
                'page_type'        => 'text/html',
                'page_lang'        => 'en',
                'page_charset'     => 'utf8',
                'page_immutable'   => 'false',
            ],
            'select'      => $a_select,
            'action'      => $action,
            'tolken'      => $_SESSION['token'],
            'form_ts'     => $_SESSION['idle_timestamp'],
            'hobbit'      => '',
            'a_menus'     => $this->retrieveNav('ManagerLinks'),
            'page_prefix' => LIB_TWIG_PREFIX
        ];
        $a_values = array_merge($a_page_values, $a_values);

        ### If we are trying to modify an existing page, grab its data and shove it into the form ###
        if ($action == 'update') {
            try {
                $a_pages = $this->o_model->readPageValues(
                    ['page_id' => $this->o_router->getPost('page_id')]
                );
            }
            catch (ModelException $e) {
                $a_pages = [];
            }

            if (isset($a_pages[0])) {
                $a_page = $a_pages[0];
                $a_values['a_page'] = $a_page;

                $label = $a_page['url_host'] == 'self'
                    ? $a_page['url_text']
                    : $a_page['url_host'] . $a_page['url_text'];

                $a_values['select']['options'][] = [
                    'value'       => $a_page['url_id'],
                    'label'       => $label,
                    'other_stuph' => ' selected'
                ];
                $a_values['select']['options'][0]['other_stuph'] = ''; // this should be the default --Select-- option
            }
            else {
                $this->logIt("Could not get the page values!", LOG_OFF, $meth . __LINE__);
            }
        }

        $tpl = '@' . LIB_TWIG_PREFIX . 'pages/page_form.twig';
        return $this->renderIt($tpl, $a_values);
    }

    /**
     * Returns the list of pages in html.
     * @param array $a_message
     * @return string
     */
    public function renderList(array $a_message = array())
    {
        $a_page_values = $this->getPageValues();
        $a_values = array(
            'page_title' => 'Manager for Page Meta Values, mostly',
            'a_message'  => [],
            'a_pages'    => [
                [
                    'page_id'        => '',
                    'page_url'       => '',
                    'page_title'     => '',
                    'page_immutable' => 'true'
                ]
            ],
            'tolken'      => $_SESSION['token'],
            'form_ts'     => $_SESSION['idle_timestamp'],
            'hobbit'      => '',
            'a_menus'     => $this->retrieveNav('ManagerLinks'),
            'adm_lvl'     => $this->adm_level,
            'page_prefix' => LIB_TWIG_PREFIX
        );
        $a_values = array_merge($a_page_values, $a_values);
        if (count($a_message) != 0) {
            $a_values['a_message'] = ViewHelper::fullMessage($a_message);
        }
        else {
            $a_values['a_message'] = '';
        }
        $a_search_for = [];
        $a_search_params = ['order_by' => 'page_immutable DESC, url_text ASC'];
        try {
            $a_pages = $this->o_model->readPageValues($a_search_for, $a_search_params);
            if (empty($a_pages)) {
                $a_pages = [];
            }
        }
        catch (ModelException $e) {
            $a_pages = [];
        }
        foreach($a_pages as $key => $a_page) {
            if ($a_page['url_host'] == 'self') {
                $a_pages[$key]['page_url'] = $a_page['url_text'];
            }
            else {
                $a_pages[$key]['page_url'] = $a_page['url_scheme'] . '://' . $a_page['url_host'] .  $a_page['url_text'];
            }
        }
        $a_values['a_pages'] = $a_pages;
        $tpl = '@' . LIB_TWIG_PREFIX . 'pages/pages.twig';
        return $this->renderIt($tpl, $a_values);
    }

    /**
     * Returns HTML verify form to delete.
     * @return string
     */
    public function renderVerify()
    {
        $meth = __METHOD__ . '.';
        $a_post_values = $this->o_router->getPost();
        $a_page = $a_post_values['page'];
        $log_message = 'Post Values ' . var_export($a_post_values, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        $a_page_values = $this->getPageValues();
        $a_values = array(
            'what'         => 'Page ',
            'name'         => $a_page['page_title'],
            'where'        => 'pages',
            'btn_value'    => 'Page',
            'hidden_name'  => 'page_id',
            'hidden_value' => $a_page['page_id'],
            'public_dir'   => PUBLIC_DIR,
            'tolken'       => $_SESSION['token'],
            'form_ts'      => $_SESSION['idle_timestamp'],
            'hobbit'       => '',
            'a_menus'      => $this->retrieveNav('ManagerLinks'),
            'adm_lvl'      => $this->adm_level,
            'page_prefix'  => LIB_TWIG_PREFIX
        );
        $a_values = array_merge($a_values, $a_page_values);
        $this->logIt('Twig Values: ' . var_export($a_values, TRUE), LOG_OFF, $meth . __LINE__);
        $tpl = '@' . LIB_TWIG_PREFIX . 'pages/verify_delete.twig';
        return $this->renderIt($tpl, $a_values);
    }
}
