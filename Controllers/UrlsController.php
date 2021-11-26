<?php /** @noinspection PhpUndefinedConstantInspection */

/**
 * Class UrlsController
 * @package Ritc_Library
 */
namespace Ritc\Library\Controllers;

use JsonException;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\Strings;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ConfigControllerInterface;
use Ritc\Library\Models\UrlsModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ControllerTraits;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Views\UrlsView;

/**
 * Controller for Urls admin.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.2.0
 * @date    2021-11-26 16:12:25
 * @change_log
 * - v1.2.0         - updated for php8                                  - 2021-11-26 wer
 * - v1.1.0         - put into production                               - 2018-06-06 wer
 * - v1.1.0-beta.1  - Refactored to match refactoring of model          - 2017-06-19 wer
 * - v1.0.0         - Out of beta                                       - 2017-06-03 wer
 * - v1.0.0-beta.2  - Change to splitUrl to allow posted url to not     - 2017-06-02 wer
 *                    include the current site.
 * - v1.0.0-beta.1  - minor change to ControllerTraits reflected here.  - 2016-04-15 wer
 * - v1.0.0-beta.0  - Initial working version                           - 2016-04-13 wer
 * - v1.0.0-alpha.0 - Initial version                                   - 2016-04-11 wer
 */
class UrlsController implements ConfigControllerInterface
{
    use LogitTraits;
    use ControllerTraits;

    /** @var UrlsModel */
    protected UrlsModel $o_urls_model;
    /** @var UrlsView */
    protected UrlsView $o_urls_view;

    /**
     * UrlsController constructor.
     *
     * @param Di $o_di
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
     *
     * @return string
     * @throws JsonException
     */
    public function route():string
    {
        $this->setProperties();
        return match ($this->form_action) {
            'verify_delete' => $this->verifyDelete(),
            'update'        => $this->update(),
            'save_new'      => $this->save(),
            'delete'        => $this->delete(),
            default         => $this->o_urls_view->renderList(),
        };
    }

    /**
     * Method for saving data.
     *
     * @return string
     * @throws JsonException
     */
    public function save():string
    {
        $meth = __METHOD__ . '.';
        $log_message = 'post:  ' . var_export($this->a_post, true);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        $url = $this->a_post['url'];
        $url = Strings::makeGoodUrl($url);
        if (empty($url)) {
            $a_message = ViewHelper::failureMessage('The URL must be a valid URL format, e.g. https://www.mydomain.com/fred/');
            return $this->o_urls_view->renderList($a_message);
        }
        $a_values  = $this->splitUrl($url);
        $a_values['url_immutable'] = isset($this->a_post['immutable']) ? 'true' : 'false';

        try {
            $this->o_urls_model->create($a_values);
            if ($this->use_cache) {
                $this->o_cache->clearTag('urls');
            }
            $a_message = ViewHelper::successMessage();
        }
        catch (ModelException) {
            $a_message = ViewHelper::failureMessage('A Problem Has Occured. The new url could not be saved.');
        }
        return $this->o_urls_view->renderList($a_message);
    }

    /**
     * Method for updating data.
     *
     * @return string
     */
    public function update():string
    {
        if (!isset($this->a_post['url_id'], $this->a_post['url'])) {
            $a_message = ViewHelper::failureMessage('A Problem Has Occurred. The url could not be updated.');
            return $this->o_urls_view->renderList($a_message);
        }
        $url = $this->a_post['url'];
        $url = Strings::makeGoodUrl($url);
        if (empty($url)) {
            $a_message = ViewHelper::failureMessage('The URL must be a valid URL format, e.g. https://www.mydomain.com/fred/');
            return $this->o_urls_view->renderList($a_message);
        }
        $a_values = $this->splitUrl($url);
        if ($a_values['url_host'] === '') {
            $a_message = ViewHelper::failureMessage('The URL must be a valid URL format, e.g. https://www.mydomain.com/fred/ or /fred/ or /fred.html');
            return $this->o_urls_view->renderList($a_message);
        }
        $a_values['url_id'] = $this->a_post['url_id'];
        $a_values['url_immutable'] = isset($this->a_post['immutable']) ? 'true' : 'false';

        try {
            $this->o_urls_model->update($a_values, ['url_text', 'url_host']);
            if ($this->use_cache) {
                $this->o_cache->clearTag('urls');
            }
            $a_message = ViewHelper::successMessage();
        }
        catch (ModelException) {
            $a_message = ViewHelper::failureMessage('A Problem Has Occured. The url could not be updated.');
        }
        return $this->o_urls_view->renderList($a_message);
    }

    /**
     * Method to display the verify delete form.
     *
     * @return string
     */
    public function verifyDelete():string
    {
        $url_id = $this->a_post['url_id'];
        $url = $this->a_post['url'];
        $immutable = !empty($this->a_post['immutable']);
        if ($immutable) {
            $msg = ViewHelper::errorMessage('Unable to delete an immutable record.');
            return $this->o_urls_view->renderList($msg);
        }
        $a_values = [
            'what'          => 'URL',
            'name'          => $url,
            'form_action'   => '/manager/config/urls/',
            'btn_value'     => 'Url',
            'hidden_name'   => 'url_id',
            'hidden_value'  => $url_id
        ];
        $a_options = [
            'fallback' => 'renderList' // if something goes wrong, which method to fallback
        ];
        return $this->o_urls_view->renderVerifyDelete($a_values, $a_options);
    }

    /**
     * Method to delete data.
     *
     * @return string
     */
    public function delete():string
    {
        $url_id = $this->a_post['url_id'] ?? -1;
        try {
            $this->o_urls_model->delete($url_id);
            if ($this->use_cache) {
                $this->o_cache->clearTag('urls');
            }
            $a_message = ViewHelper::successMessage();
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::failureMessage('A Problem Has Occured. ' . $e->errorMessage());
        }
        return $this->o_urls_view->renderList($a_message);
    }

    /**
     * Splits the url into 3 components, scheme, host, and the rest of the url.
     *
     * @param string $url
     * @return array
     */
    private function splitUrl(string $url = ''):array
    {
        if (empty(strpos($url, '://'))) {
            $scheme = SITE_PROTOCOL;
            $host   = 'self';
            $text   = $url;
        }
        else {
            [$scheme, $text] = explode('://', $url);
            $scheme = Strings::makeValidUrlScheme($scheme);
            $first_slash = strpos($text, '/');
            $host = substr($text, 0, $first_slash);
            $text = substr($text, $first_slash);
        }

        if (!str_starts_with($text, '/')) {
            $text = '/' . $text;
        }
        if (strrpos($text, '.') === false && $text[strlen($text) - 1] !== '/') {
            $text .= '/';
        }
        return [
            'url_scheme' => $scheme,
            'url_host'   => $host,
            'url_text'   => $text
        ];
    }
}
