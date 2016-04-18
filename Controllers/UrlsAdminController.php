<?php
/**
 * @brief     Controller for Urls admin.
 * @ingroup   lib_controllers
 * @file      Ritc/Library/Controllers/UrlsAdminController.php
 * @namespace Ritc\Library\Controllers
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-beta.1
 * @date      2016-04-15 12:44:28
 * @note Change Log
 * - v1.0.0-beta.1  - minor change to ControllerTraits reflected here.  - 2016-04-15 wer
 * - v1.0.0-beta.0  - Initial working version                           - 2016-04-13 wer
 * - v1.0.0-alpha.0 - Initial version                                   - 2016-04-11 wer
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ManagerControllerInterface;
use Ritc\Library\Models\UrlsModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ControllerTraits;
use Ritc\Library\Views\UrlsView;

/**
 * Class UrlsAdminController.
 * @class   UrlsAdminController
 * @package Ritc\Library\Controller
 */
class UrlsAdminController implements ManagerControllerInterface
{
    use ControllerTraits;

    protected $o_urls_model;
    protected $o_urls_view;

    /**
     * UrlsAdminController constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupController($o_di);
        $this->o_urls_model = new UrlsModel($this->o_db);
        $this->o_urls_view  = new UrlsView($o_di);
        if (DEVELOPER_MODE) {
            $this->o_urls_model->setElog($this->o_elog);
        }
    }

    /**
     * Main method used to render the page.
     * @return string
     */
    public function render()
    {
        $this->setProperties();
        switch ($this->main_action) {
            case 'modify':
                switch ($this->form_action) {
                    case 'verify':
                        return $this->verifyDelete();
                    case 'update':
                        return $this->update();
                    default:
                        $a_message = ViewHelper::failureMessage('A problem occured. Please try again.');
                        return $this->o_urls_view->renderList($a_message);
                }
            case 'save':
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
        $url = $this->a_post['url'];
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            $a_message = ViewHelper::failureMessage('The URL must be a valid URL format, e.g. http://www.mydomain.com/fred/');
            return $this->o_urls_view->renderList($a_message);
        }
        $a_values  = $this->splitUrl($url);
        $a_values['url_immutable'] = isset($this->a_post['immutable']) ? 1 : 0;

        $results = $this->o_urls_model->create($a_values);
        if ($results !== false) {
            $a_message = ViewHelper::successMessage();
        }
        else {
            $a_message = ViewHelper::failureMessage('A Problem Has Occured. The new url could not be saved.');
        }
        $log_message = 'Message ' . var_export($a_message, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        return $this->o_urls_view->renderList($a_message);
    }

    /**
     * Method for updating data.
     * @return string
     */
    public function update()
    {
        if (!isset($this->a_post['url_id']) || !isset($this->a_post['url'])) {
            $a_message = ViewHelper::failureMessage('A Problem Has Occured. The url could not be updated.');
            return $this->o_urls_view->renderList($a_message);
        }
        $url = $this->a_post['url'];
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            $a_message = ViewHelper::failureMessage('The URL must be a valid URL format, e.g. http://www.mydomain.com/fred/');
            return $this->o_urls_view->renderList($a_message);
        }
        $a_values                  = $this->splitUrl($url);
        $a_values['url_id']        = (int) $this->a_post['url_id'];
        $a_values['url_immutable'] = isset($this->a_post['immutable']) ? 1 : 0;

        $results = $this->o_urls_model->update($a_values);
        if ($results !== false) {
            $a_message = ViewHelper::successMessage();
        }
        else {
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
        $results = $this->o_urls_model->delete($url_id);
        if ($results !== false) {
            $a_message = ViewHelper::successMessage();
        }
        else {
            $a_message = ViewHelper::failureMessage('A Problem Has Occured. The new configuration could not be saved.');
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
        list($scheme, $text) = explode('://', $url);

        if (!$this->isValidScheme($scheme)) {
            $scheme = 'https';
        }

        $first_slash = strpos($text, '/');
        $host = substr($text, 0, $first_slash);
        if ($host == $_SERVER['HTTP_HOST']) {
            $host = 'self';
        }
        $text = substr($text, $first_slash);
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