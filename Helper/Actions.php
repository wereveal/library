<?php
/**
 * Class Actions
 * @package Ritc_Library
 */
namespace Ritc\Library\Helper;

/**
 * Class Actions - Extracts the action to use for the page.
 * @details Can get the action from the URL - htaccess required
 *          or from a form element with a few select id names
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 4.0.0
 * @date    2021-11-29 14:55:18
 * @change_log
 * - 4.0.0 - updated for php8                                                   - 2021-11-29 wer
 * - 3.0.0 - bug fixes and change version to match better semantic versioning   - 2019-03-28 wer
 * - 2.2.7 - match refactoring of Strings and Arrays to static methods          - 01/27/2015 wer
 * - 2.2.6 - moved file into Helper area                                        - 11/15/2014 wer
 * - 2.2.5 - changed to implment the changes in Base class                      - 09/23/2014 wer
 * - 2.2.4 - changed to match namespace change                                  - 12/19/2013 wer
 * - 2.2.3 - Changed to namespace reorge
 * - 2.2.2 - changed to new namespace                                           - 03/27/2013 wer
 * - 2.2.1 - added a bit more sanitation to uri actions,
 *           renamed action to form_action to be clearer what it was
 * - 2.2.0 - refactored to be closer to FIG standards
 */
class Actions
{
    /** @var array */
    protected array $a_clean_post;
    /** @var array */
    protected array $a_clean_get;
    /** @var array */
    protected array $a_uri_actions;
    /** @var string */
    protected string $form_action;
    /** @var string */
    protected string $uri_no_get = '';
    /** @var string */
    protected string $url_path;

    /**
     * Actions constructor.
     */
    public function __construct()
    {
        $this->setCleanPost($_POST);
        $this->setCleanGet($_GET);
        $this->setUriNoGet($_SERVER['REQUEST_URI']);
        $this->setUriActions();
        $this->setFormAction(array_merge($this->a_clean_get, $this->a_clean_post));
        $this->setUrlPath($this->uri_no_get);
    }

    /**
     * @return string
     */
    public function getFormAction():string
    {
        return $this->form_action;
    }

    /**
     * Returns a value from the class property a_clean_post or the entire array.
     * Returns an empty value if the key does not exist in class property.
     *
     * @param string $key_name
     * @return array
     */
    public function getCleanPost(string $key_name = ''):array
    {
        if ($key_name === '') {
            return $this->a_clean_post;
        }
        return $this->a_clean_post[$key_name] ?? [];
    }

    /**
     * @param string $value
     * @return string|array
     */
    public function getCleanGet(string $value = ''): array|string
    {
        if ($value === '') {
            return $this->a_clean_get;
        }
        return $this->a_clean_get[$value] ?? '';
    }

    /**
     * @return string
     */
    public function getFilePath():string
    {
        /* returns the full path where a file is located */
        $a_file_path = explode('/', __FILE__);
        $the_count = count($a_file_path);
        $the_output = '/';
        for ($i = 0; $i<$the_count-1; $i++) {
            if ($a_file_path[$i] !== '') {
                $the_output .= $a_file_path[$i] . '/';
            }
        }
        return $the_output;
    }

    /**
     * @param array $array
     * @return string
     */
    public function getImageSubmit(array $array = []): string
    {
        if (!empty($array)) {
            foreach ($array as $key => $value) {
                $x = strlen($key) - 2;
                if (substr($key, $x, 2) === '_x') {
                    return substr($key, 0, $x);
                }
            }
            return '';
        }
        return '';
    }

    /**
     * @return array
     */
    public function getUriActions(): array
    {
        return $this->a_uri_actions;
    }

    /**
     * @return string
     */
    public function getUrlPath(): string
    {
        return $this->url_path;
    }

    /**
     * @param $a_clean_post
     * @return bool
     */
    public function setFormAction($a_clean_post):bool
    {     // used $a_clean_post as a not so subtle hint to use a safe array and not raw $_POST/$_GET/$_REQUEST etc
        $x_action = '';
        $y_action = '';
        foreach ($a_clean_post as $key => $value) {
            if (str_ends_with($key, '_x')) {
                $x_action = substr($key, 0, strpos($key, '_x'));
            }
            elseif (str_ends_with($key, '_y')) {
                $y_action = substr($key, 0, strpos($key, '_y'));
            }
        }
        if (isset($a_clean_post['action']) && ($a_clean_post['action'] !== '')) {
            $action = Strings::makeInternetUsable($a_clean_post['action']);
        }
        elseif (isset($a_clean_post['step']) && ($a_clean_post['step'] !== '')) {
            $action = Strings::makeInternetUsable($a_clean_post['step']);
        }
        elseif (isset($a_clean_post['submit']) && ($a_clean_post['submit'] !== '')) {
            $action = Strings::makeInternetUsable($a_clean_post['submit']);
        }
        elseif (($x_action !== '') && ($x_action === $y_action)) {
            $action = Strings::makeInternetUsable($x_action);
        }
        else {
            $action = '';
        }
        $this->form_action = $action;
        return true;
    }

    /**
     * @param array $array
     * @param array $a_allowed_keys
     * @return bool
     */
    public function setCleanPost(array $array = array(), array $a_allowed_keys = array()):bool
    {
        if (count($array) === 0) {
            $this->a_clean_post = array();
            return true;
        }
        $this->a_clean_post = Arrays::cleanArrayValues($array, $a_allowed_keys);
        return true;
    }

    /**
     * @param array $array
     * @param array $a_allowed_keys
     * @return bool
     */
    public function setCleanGet(array $array = [], array $a_allowed_keys = []):bool
    {
        if (empty($array)) {
            $this->a_clean_get = array();
            return true;
        }
        $this->a_clean_get = Arrays::cleanArrayValues($array, $a_allowed_keys);
        return true;
    }

    /**
     * Sets the class property uri_no_get.
     * Uri without any query stuff, otherwise known as the GET stuff.
     *
     * @param string $request_uri
     */
    public function setUriNoGet(string $request_uri = ''):void
    {
        if ($request_uri === '') {
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        }
        if (str_contains($request_uri, '?')) {
            $this->uri_no_get = substr($request_uri, 0, strpos($request_uri, '?'));
        }
        else {
            $this->uri_no_get = $request_uri;
        }
    }

    /**
     * @param string $root_dir
     * @return bool
     */
    public function setUriActions(string $root_dir = ''):bool
    {
        /* $root_dir defines what isn't an action in the URI */
        $a_actions   = [];
        $uri_actions = str_replace($root_dir, '', $this->uri_no_get);
        $uri_parts   = explode('/', $uri_actions);
        foreach ($uri_parts as $key => $value) {
            if (($value === '') || (str_contains($value, '.php'))) {
                unset($uri_parts[$key]);
            }
        }
        $uri_parts = array_merge($uri_parts);
        $uri_count = count($uri_parts);
        if ($uri_count > 0) {
            $array_part = 0;
            for ($x=1; $x<=$uri_count; $x++) {
                $a_actions['action' .$x] = $uri_parts[$array_part];
                $array_part++;
            }
        }
        else {
            $this->a_uri_actions = array();
            return false;
        }
        $this->a_uri_actions = $this->filterUriActions($a_actions);
        return true;
    }

    /**
     * @param string $uri
     * @return bool
     */
    public function setUrlPath(string $uri = ''):bool
    {
        $uri = ($uri === '') ? $this->uri_no_get : $uri;
        $a_file_path = explode('/', $uri);
        $the_count = count($a_file_path);
        $the_output = '/';
        for ($i=0; $i<$the_count-1; $i++) {
            if ($a_file_path[$i] !== '' && (str_contains($a_file_path[$i], '.php')))  {
                $the_output .= $a_file_path[$i] . '/';
            }
        }
        $this->url_path = $the_output;
        return true;
    }

    ### Utility ###

    /**
     * @param $a_actions
     * @return array
     */
    private function filterUriActions($a_actions):array
    {
        $new_array = array();
        foreach ($a_actions as $key => $value ) {
            $new_array[$key] = filter_var($value, FILTER_SANITIZE_STRING);
        }
        return $new_array;
    }
}
