<?php
/**
 *  @brief View for the Page Admin page.
 *  @file PageAdminView.php
 *  @ingroup ritc_library views
 *  @namespace Ritc/Library/Views
 *  @class PageAdminView
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0β1
 *  @date 2015-10-30 08:58:33
 *  @note <pre><b>Change Log</b>
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
     *  Returns the list of routes in html.
     *  @param array $a_message
     *  @return string
     */
    public function renderList(array $a_message = array())
    {
        $a_values = array(
            'public_dir' => '',
            'description' => 'Admin page for the router configuration.',
            'a_message' => array(),
            'a_page' => array(
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
        if (count($a_message) != 0) {
            $a_values['a_message'] = ViewHelper::messageProperties($a_message);
        }
        else {
            $a_values['a_message'] = ViewHelper::messageProperties(
                array(
                    'message'       => 'Changing page values can result in unexpected results. If you are not sure, do not do it.',
                    'type'          => 'warning'
                )
            );
        }
        $a_page = $this->o_model->read(array(), ['order_by' => 'page_immutable DESC, page_url']);
        $this->logIt(
            'a_page: ' . var_export($a_page, TRUE),
            LOG_OFF,
            __METHOD__ . '.' . __LINE__
        );
        if ($a_page !== false && count($a_page) > 0) {
            $a_values['a_page'] = $a_page;
        }
        return $this->o_twig->render('@pages/page_admin.twig', $a_values);
    }
    /**
     *  Returns HTML verify form to delete.
     *  @param array $a_values
     *  @return string
     */
    public function renderVerify(array $a_values = array())
    {
        if ($a_values === array()) {
            return $this->renderList(['message' => 'An Error Has Occurred. Please Try Again.', 'type' => 'failure']);
        }
        if (!isset($a_values['public_dir'])) {
            $a_values['public_dir'] = '';
        }
        if (!isset($a_values['description'])) {
            $a_values['description'] = 'Form to verify the action to delete the page.';
        }
        $a_values['menus'] = $this->a_links;
        return $this->o_twig->render('@pages/verify_delete_page.twig', $a_values);
    }
}
