<?php
/**
 *  @brief     View for the Page Admin page.
 *  @ingroup   ritc_library views
 *  @file      PageAdminView.php
 *  @namespace Ritc\Library\Views
 *  @class     PageAdminView
 *  @author    William E Reveal <bill@revealitconsulting.com>
 *  @version   1.0.0
 *  @date      2015-11-27 15:02:30
 *  @note <pre><b>Change Log</b>
 *      v1.0.0   - take out of beta                             - 11/27/2015 wer
 *      v1.0.0β1 - Initial version                              - 10/30/2015 wer
 *  </pre>
**/
namespace Ritc\Library\Views;

use Ritc\Library\Models\PageModel;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Traits\ManagerViewTraits;

class PageAdminView
{
    use LogitTraits, ManagerViewTraits;

    private $o_model;

    public function __construct(Di $o_di)
    {
        $this->setupView($o_di);
        $this->o_model = new PageModel($this->o_db);
        if (DEVELOPER_MODE) {
            $this->o_elog = $o_di->get('elog');
            $this->o_model->setElog($this->o_elog);
        }
    }
    /**
     *  Returns a form to enter page data into db.
     *  @param array $a_message
     *  @return string
     */
    public function renderForm(array $a_message = array())
    {
        $meth = __METHOD__ . '.';
        $a_page_values = $this->getPageValues();
        $action = $this->o_router->getFormAction() == 'new_page'
            ? 'save'
            : 'update';
        $a_values = [
            'adm_lvl' => $this->adm_level,
            'a_message' => $a_message,
            'a_page'    => [
                'page_id'          => '',
                'page_url'         => '',
                'page_title'       => '',
                'page_description' => '',
                'page_base_url'    => '/',
                'page_type'        => 'text/html',
                'page_lang'        => 'en',
                'page_charset'     => 'utf8',
                'page_immutable'   => 0
            ],
            'action'  => $action,
            'tolken'  => $_SESSION['token'],
            'form_ts' => $_SESSION['idle_timestamp'],
            'hobbit'  => '',
            'menus'   => $this->a_links
        ];
        $a_values = array_merge($a_page_values, $a_values);
        if ($action == 'update') {
            $a_pages = $this->o_model->read(
                ['page_id' => $this->o_router->getPost('page_id')]
            );
            if ($a_pages != array()) {
                $a_values['a_page'] = $a_pages[0];
            }
            $log_message = 'a_values: ' . var_export($a_values, TRUE);
            $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
        }
        return $this->o_twig->render('@pages/page_form.twig', $a_values);
    }
    /**
     *  Returns the list of pages in html.
     *  @param array $a_message
     *  @return string
     */
    public function renderList(array $a_message = array())
    {
        $a_page_values = $this->getPageValues();
        $a_values = array(
            'a_message' => array(),
            'a_pages'   => array(
                [
                    'page_id'        => '',
                    'page_url'       => '',
                    'page_title'     => '',
                    'page_immutable' => 1
                ]
            ),
            'tolken'  => $_SESSION['token'],
            'form_ts' => $_SESSION['idle_timestamp'],
            'hobbit'  => '',
            'menus'   => $this->a_links,
            'adm_lvl' => $this->adm_level
        );
        $a_values = array_merge($a_page_values, $a_values);
        if (count($a_message) != 0) {
            $a_values['a_message'] = ViewHelper::messageProperties($a_message);
        }
        else {
            $a_values['a_message'] = ViewHelper::messageProperties([
                'message' => 'Changing page values can result in unexpected results. If you are not sure, do not do it.',
                'type'    => 'warning'
            ]);
        }
        $a_pages = $this->o_model->read(array(), ['order_by' => 'page_immutable DESC, page_url']);
        $this->logIt(
            'a_page: ' . var_export($a_pages, TRUE),
            LOG_OFF,
            __METHOD__ . '.' . __LINE__
        );
        if ($a_pages !== false && count($a_pages) > 0) {
            $a_values['a_pages'] = $a_pages;
        }
        return $this->o_twig->render('@pages/page_admin.twig', $a_values);
    }
    /**
     *  Returns HTML verify form to delete.
     *  @param array $a_values
     *  @return string
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
            'what'         => 'Page ' . $a_page['page_title'],
            'name'         => $a_page['page_url'],
            'where'        => 'pages',
            'btn_value'    => 'Page',
            'hidden_name'  => 'page_id',
            'hidden_value' => $a_page['page_id'],
            'public_dir'   => PUBLIC_DIR,
            'tolken'       => $_SESSION['token'],
            'form_ts'      => $_SESSION['idle_timestamp'],
            'hobbit'       => '',
            'menus'        => $this->a_links,
            'adm_lvl'      => $this->adm_level
        );
        $a_values = array_merge($a_values, $a_page_values);
        $this->logIt('Twig Values: ' . var_export($a_values, TRUE), LOG_OFF, $meth . __LINE__);
        return $this->o_twig->render('@pages/verify_delete.twig', $a_values);
    }
}
