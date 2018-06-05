<?php
/**
 * Class UrlsController
 * @package Ritc_Library
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ManagerControllerInterface;
use Ritc\Library\Models\UrlsModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ControllerTraits;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Views\UrlsView;

/**
 * Controller for Urls admin.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.1.0
 * @date    2017-06-03 17:17:30
 * @change_log
 * - v1.1.0-beta.1  - Refactored to match refactoring of model          - 2017-06-19 wer
 * - v1.0.0         - Out of beta                                       - 2017-06-03 wer
 * - v1.0.0-beta.2  - Change to splitUrl to allow posted url to not     - 2017-06-02 wer
 *                    include the current site.
 * - v1.0.0-beta.1  - minor change to ControllerTraits reflected here.  - 2016-04-15 wer
 * - v1.0.0-beta.0  - Initial working version                           - 2016-04-13 wer
 * - v1.0.0-alpha.0 - Initial version                                   - 2016-04-11 wer
 */
class UrlsController implements ManagerControllerInterface
{
    use ControllerTraits, LogitTraits;

    /** @var \Ritc\Library\Models\UrlsModel  */
    protected $o_urls_model;
    /** @var \Ritc\Library\Views\UrlsView  */
    protected $o_urls_view;

    /**
     * UrlsController constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupController($o_di);
        $this->o_urls_model = new UrlsModel($this->o_db);
        $this->o_urls_view  = new UrlsView($o_di);
        $this->a_object_names = ['o_urls_model'];
        $this->setupElog($o_di);
    }

    /**
     * Main method used to render the page.
     * @return string
     */
    public function route()
    {
        $this->setProperties();
        switch ($this->form_action) {
            case 'verify_delete':
                return $this->verifyDelete();
            case 'update':
                return $this->update();
            case 'save_new':
                return $this->save();
            case 'delete':
                return $this->delete();
            default:
                return $this->o_urls_view->renderList();
        }
    }

    /**
     * Method for saving data.
     * @return string
     */
    public function save()
    {
        $meth = __METHOD__ . '.';
        $log_message = 'post:  ' . var_export($this->a_post, true);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        $url = $this->a_post['url'];
        if (!empty(strpos($url, '://'))) {
            if (filter_var($url, FILTER_VALIDATE_URL) === false) {
                $a_message = ViewHelper::failureMessage('The URL must be a valid URL format, e.g. http://www.mydomain.com/fred/');
                return $this->o_urls_view->renderList($a_message);
            }
        }
        $a_values  = $this->splitUrl($url);
        $a_values['url_immutable'] = isset($this->a_post['immutable']) ? 'true' : 'false';

        try {
            $this->o_urls_model->create($a_values);
            $a_message = ViewHelper::successMessage();
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::failureMessage('A Problem Has Occured. The new url could not be saved.');
        }
        return $this->o_urls_view->renderList($a_message);
    }

    /**
     * Method for updating data.
     * @return string
     */
    public function update()
    {
        $meth = __METHOD__ . '.';
        $log_message = 'Post ' . var_export($this->a_post, true);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        if (!isset($this->a_post['url_id']) || !isset($this->a_post['url'])) {
            $a_message = ViewHelper::failureMessage('A Problem Has Occurred. The url could not be updated.');
            return $this->o_urls_view->renderList($a_message);
        }
        $url = $this->a_post['url'];
        if (!empty(strpos($url, '://'))) {
            if (filter_var($url, FILTER_VALIDATE_URL) === false) {
                $a_message = ViewHelper::failureMessage('The URL must be a valid URL format, e.g. http://www.mydomain.com/fred/');
                return $this->o_urls_view->renderList($a_message);
            }
        }
        $a_values = $this->splitUrl($url);
        $a_values['url_id'] = $this->a_post['url_id'];
        $a_values['url_immutable'] = isset($this->a_post['immutable']) ? 'true' : 'false';

        $log_message = 'final values ' . var_export($a_values, true);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        try {
            $this->o_urls_model->update($a_values);
            $a_message = ViewHelper::successMessage();
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::failureMessage('A Problem Has Occured. The url could not be updated.');
        }
        return $this->o_urls_view->renderList($a_message);
    }

    /**
     * Method to display the verify delete form.
     * @return string
     */
    public function verifyDelete()
    {
        return $this->o_urls_view->renderVerify($this->a_post);
    }

    /**
     * Method to delete data.
     * @return string
     */
    public function delete()
    {
        $url_id = isset($this->a_post['url_id']) ? $this->a_post['url_id'] : -1;
        try {
            $this->o_urls_model->delete($url_id);
            $a_message = ViewHelper::successMessage();
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::failureMessage('A Problem Has Occured. ' . $e->errorMessage());
        }
        return $this->o_urls_view->renderList($a_message);
    }

    /**
     * Verifies that the scheme is a valid one for the app.
     * @param string $value
     * @return bool
     */
    private function isValidScheme($value = '')
    {
        switch ($value) {
            case 'http':
            case 'https':
            case 'ftp':
            case 'gopher':
            case 'mailto':
            case 'file':
                return true;
            default:
                return false;
        }
    }

    /**
     * Splits the url into 3 components, scheme, host, and the rest of the url.
     * @param string $url
     * @return array
     */
    private function splitUrl($url = '')
    {
        if (empty(strpos($url, '://'))) {
            $scheme = SITE_PROTOCOL;
            $host   = 'self';
            $text   = $url;
        }
        else {
            list($scheme, $text) = explode('://', $url);
            if (!$this->isValidScheme($scheme)) {
                $scheme = 'https';
            }
            $first_slash = strpos($text, '/');
            $host = substr($text, 0, $first_slash);
            $text = substr($text, $first_slash);
        }

        if (substr($text, 0, 1) != '/') {
            $text = '/' . $text;
        }
        if (strrpos($text, '.') === false) {
            if (substr($text, -1, 1) != '/') {
                $text .= '/';
            }
        }
        $return_this = [
            'url_scheme' => $scheme,
            'url_host'   => $host,
            'url_text'   => $text
        ];
        return $return_this;
    }
}
