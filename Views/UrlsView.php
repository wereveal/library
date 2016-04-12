<?php
/**
 * @brief     The view for the urls manager.
 * @ingroup   lib_views
 * @file      UrlsView.php
 * @namespace Ritc\Library\Views
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.0
 * @date      2016-04-11 08:41:34
 * @note Change Log
 * - v1.0.0-alpha.0 - Initial version        - 2016-04-11 wer
 */
namespace Ritc\Library\Views;

use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Models\UrlsModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ViewTraits;

/**
 * Class UrlsView.
 * @class   UrlsView
 * @package Ritc\Library\Views
 */
class UrlsView
{
    use ViewTraits;

    protected $o_urls_model;

    /**
     * UrlsView constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupView($o_di);
        $this->o_urls_model = new UrlsModel($this->o_db);
        if (DEVELOPER_MODE) {
            $this->o_urls_model->setElog($this->o_elog);
        }
    }

    /**
     * Renders the list of URLs.
     * @param array $a_message
     * @return string
     */
    public function renderList(array $a_message = [])
    {
        $meth = __METHOD__ . '.';
        $a_urls = $this->o_urls_model->read();
        $log_message = 'URLs returned ' . var_export($a_urls, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        $a_new_urls = [];
        foreach($a_urls as $a_url) {
            $a_new_urls[] = [
                'url_id'    => $a_url['url_id'],
                'url'       => $a_url['url_scheme'] . '://' . $_SERVER['HTTP_HOST'] . $a_url['url_text'],
                'immutable' => $a_url['url_immutable'] == 1 ? 'true' : 'false'
            ];
        }
        if (count($a_message) != 0) {
            $a_message['message'] .= "<br>Changing the URL can result in unexpected results. If you are not sure, do not do it.";
            $a_message = ViewHelper::messageProperties($a_message);
        }
        else {
            $a_message = ViewHelper::messageProperties(
                [
                    'message' => 'Changing the URL can result in unexpected results. If you are not sure, do not do it.',
                    'type'    => 'warning'
                ]
            );
        }

        $a_values = [
            'a_menus'     => $this->retrieveNav('ManagerLinks'),
            'a_urls'      => $a_new_urls,
            'a_message'   => $a_message,
            'tolken'      => $_SESSION['token'],
            'form_ts'     => $_SESSION['idle_timestamp'],
            'hobbit'      => '',
            'adm_lvl'     => $this->adm_level,
            'twig_prefix' => LIB_TWIG_PREFIX
        ];
        $log_message = 'Twig Values:  ' . var_export($a_values, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        $tpl = '@' . LIB_TWIG_PREFIX . 'pages/urls_admin.twig';
        return $this->o_twig->render($tpl, $a_values);
    }

    /**
     * Renders the verify delete record form.
     * @param $a_values
     * @return string
     */
    public function renderVerify($a_values)
    {
        if ($a_values === array()) {
            $a_message = ViewHelper::messageProperties(['message' => 'An Error Has Occurred. Please Try Again.', 'type' => 'failure']);
            return $this->renderList($a_message);
        }
        $a_page_values = $this->getPageValues(); // provided in ViewTraits
        $a_twig_values = [
            'what'         => 'URL',
            'name'         => $a_values['url'],
            'where'        => 'urls',
            'btn_value'    => 'Url',
            'hidden_name'  => 'url_id',
            'hidden_value' => $a_values['url_id'],
            'tolken'       => $a_values['tolken'],
            'form_ts'      => $a_values['form_ts'],
            'a_menus'      => $this->retrieveNav('ManagerLinks'),
            'twig_prefix'  => LIB_TWIG_PREFIX
        ];
        if (isset($a_values['public_dir'])) {
            $a_twig_values['public_dir'] = $a_values['public_dir'];
        }
        $a_twig_values = array_merge($a_twig_values, $a_page_values);
        $tpl = '@' . LIB_TWIG_PREFIX . 'pages/verify_delete.twig';
        return $this->o_twig->render($tpl, $a_twig_values);
    }
}