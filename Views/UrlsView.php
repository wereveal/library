<?php
namespace Ritc\Library\Views;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Models\UrlsModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigViewTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class UrlsView.
 *
 * @package RITC_Library
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.1.0
 * @date    2017-06-20 11:53:31
 * ## Change Log
 * - v1.1.0         - ViewHelper renaming of a method reflected here.   - 2017-06-20 wer
 *                    ModelException added
 * - v1.0.0         - Out of beta                                       - 2017-06-03 wer
 */
class UrlsView
{
    use LogitTraits, ConfigViewTraits;

    /** @var \Ritc\Library\Models\UrlsModel  */
    protected $o_urls_model;

    /**
     * UrlsView constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupElog($o_di);
        $this->setupView($o_di);
        $this->o_urls_model = new UrlsModel($this->o_db);
        $this->o_urls_model->setupElog($o_di);
    }

    /**
     * Renders the list of URLs.
     * @param array $a_message
     * @return string
     */
    public function renderList(array $a_message = [])
    {
        try {
            $a_urls = $this->o_urls_model->read();
        }
        catch (ModelException $e) {
            $a_urls = [];
            $a_message['message'] .= 'Error occurred reading the url list: ' . $e->errorMessage();
        }
        $a_new_urls = [];
        foreach($a_urls as $a_url) {
            if ($a_url['url_host'] == 'self') {
                $url = $a_url['url_text'];
            }
            else {
                $url = $a_url['url_scheme'] . '://' . $a_url['url_host'] . $a_url['url_text'];
            }
            $a_new_urls[] = [
                'url_id'    => $a_url['url_id'],
                'url'       => $url,
                'immutable' => $a_url['url_immutable'] == 'true' ? 'true' : 'false'
            ];
        }
        if (!empty($a_message['message'])) {
            $a_message['message'] .= "<br>Changing the URL can result in unexpected results. If you are not sure, do not do it.";
            $a_message = ViewHelper::fullMessage($a_message);
        }
        else {
            $a_message = ViewHelper::fullMessage(
                [
                    'message' => 'Changing the URL can result in unexpected results. If you are not sure, do not do it.',
                    'type'    => 'warning'
                ]
            );
        }

        $a_twig_values = $this->createDefaultTwigValues($a_message, '/manager/config/urls/');
        $a_twig_values['a_urls'] = $a_new_urls;
        $tpl = $this->createTplString($a_twig_values);
        return $this->renderIt($tpl, $a_twig_values);
    }

    /**
     * Renders the verify delete record form.
     * @param $a_values
     * @return string
     */
    public function renderVerify($a_values)
    {
        if ($a_values === array()) {
            $a_message = ViewHelper::fullMessage(['message' => 'An Error Has Occurred. Please Try Again.', 'type' => 'failure']);
            return $this->renderList($a_message);
        }
        $a_twig_values = $this->createDefaultTwigValues([], '/manager/config/urls/');
        $a_twig_values['tpl']          = 'verify_delete';
        $a_twig_values['what']         = 'URL';
        $a_twig_values['name']         = $a_values['url'];
        $a_twig_values['hidden_name']  = 'url_id';
        $a_twig_values['hidden_value'] = $a_values['url_id'];
        $a_twig_values['btn_value']    = 'URL';
        $tpl = $this->createTplString($a_twig_values);
        return $this->renderIt($tpl, $a_twig_values);
    }
}
