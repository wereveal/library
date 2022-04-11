<?php
/**
 * Class RoutesHelper
 *
 * @package Ritc_Library
 */

namespace Ritc\Library\Helper;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Models\GroupsModel;
use Ritc\Library\Models\RoutesComplexModel;
use Ritc\Library\Models\RoutesGroupMapModel;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;

/**
 * Helper for routing.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 4.0.0
 * @date    2021-11-29 16:57:46
 * @change_log
 * - v4.0.0   - updated for php8                                    - 2021-11-29 wer
 * - v3.1.0   - Bug fix that added value to a_route_parts           - 2019-01-28 wer
 * - v3.0.0   - Changed to use cache for some database values       - 2018-05-14 wer
 * - v2.1.0   - Changed to handle ModelExceptions                   - 2017-06-20 wer
 * - v2.0.0   - Changed to handle inexact request URI (slashes)     - 2016-09-08 wer
 * - v1.1.0   - added method for quick min auth level for a route.  - 02/26/2016 wer
 * - v1.0.0   - took out of beta                                    - 11/27/2015 wer
 * - v1.0.0β1 - initial file                                        - 09/26/2015 wer
 */
class RoutesHelper
{
    use LogitTraits;

    /** @var array */
    private array $a_route_parts;
    private string $cache_type;
    /** @var CacheHelperSymfony $o_cache */
    private CacheHelperSymfony $o_cache;
    /** @var DbModel */
    private DbModel $o_db;
    /** @var Di */
    private Di $o_di;
    /** @var string */
    private string $route_path;
    /** @var string */
    private string $request_uri;
    /** @var bool */
    private bool $use_cache = false;

    /**
     * RoutesHelper constructor.
     *
     * @param Di     $o_di
     * @param string $request_uri
     * @noinspection PhpFieldAssignmentTypeMismatchInspection
     */
    public function __construct(Di $o_di, string $request_uri = '')
    {
        $this->setupElog($o_di);
        $this->o_di = $o_di;
        $this->o_db = $o_di->get('db');
        if (USE_CACHE) {
            $o_cache = $o_di->get('cache');
            if (!empty($o_cache)) {
                $this->o_cache    = $o_cache;
                $this->cache_type = $this->o_cache->getCacheType();
                $this->use_cache  = !empty($this->cache_type);
            }
        }
        $this->route_path = $request_uri;
    }

    /**
     * Compare request uri with route path.
     * Determines if the only difference between the request uri and the route path
     * is a missing or unneeded slash at the end or beginning.
     *
     * @param string $request_uri required
     * @param string $route_path  required
     * @return bool
     */
    public function compareUriToRoute(string $request_uri = '', string $route_path = ''): bool
    {
        if ($request_uri === $route_path) {
            return true;
        }
        $a_compare_uri = $this->createComparisonUri($request_uri);
        foreach ($a_compare_uri as $uri) {
            if ($uri === $route_path) {
                return true;
            }
        }
        return false;
    }

    /**
     * Creates an array for possible uri to search for in database.
     *
     * @param string $request_uri
     * @return array
     */
    private function createComparisonUri(string $request_uri = ''): array
    {
        if ($request_uri === '') {
            return [
                'original'   => $request_uri,
                'lastplus'   => '',
                'lastminus'  => '',
                'startplus'  => '',
                'startminus' => '',
                'both'       => '',
                'reversed'   => '',
                'none'       => ''
            ];
        }
        $first_slash         = strpos($request_uri, '/');
        $last_slash          = strrpos($request_uri, '/');
        $possible_last_slash = strlen($request_uri) - 1;

        /* example 'test/test' */
        if ($first_slash !== 0 && ($last_slash !== $possible_last_slash)) {
            return [
                'original'   => $request_uri,
                'lastplus'   => $request_uri . '/',
                'lastminus'  => $request_uri,
                'startplus'  => '/' . $request_uri,
                'startminus' => $request_uri,
                'both'       => '/' . $request_uri . '/',
                'reversed'   => '/' . $request_uri . '/',
                'none'       => $request_uri
            ];
        }
        /* example 'test/test/' */
        if ($first_slash !== 0 && ($last_slash === $possible_last_slash)) {
            return [
                'original'   => $request_uri,
                'lastplus'   => $request_uri,
                'lastminus'  => substr($request_uri, 0, -1),
                'startplus'  => '/' . $request_uri,
                'startminus' => $request_uri,
                'both'       => '/' . $request_uri,
                'reversed'   => '/' . substr($request_uri, 0, -1),
                'none'       => substr($request_uri, 0, -1)
            ];
        }

        /* example '/test/test/' */
        if ($first_slash === 0 && ($last_slash === $possible_last_slash)) {
            return [
                'original'   => $request_uri,
                'lastplus'   => $request_uri,
                'lastminus'  => substr($request_uri, 0, -1),
                'startplus'  => $request_uri,
                'startminus' => substr($request_uri, 1),
                'both'       => $request_uri,
                'reversed'   => substr(substr($request_uri, 1), 0, -1),
                'none'       => substr(substr($request_uri, 1), 0, -1)
            ];
        }

        /* example '/test/test' */
        if ($first_slash === 0 && ($last_slash !== $possible_last_slash)) {
            return [
                'original'   => $request_uri,
                'lastplus'   => $request_uri . '/',
                'lastminus'  => $request_uri,
                'startplus'  => $request_uri,
                'startminus' => substr($request_uri, 1),
                'both'       => $request_uri . '/',
                'reversed'   => substr($request_uri, 1) . '/',
                'none'       => substr($request_uri, 1)
            ];
        }

        return [
            'original'   => $request_uri,
            'lastplus'   => '',
            'lastminus'  => '',
            'startplus'  => '',
            'startminus' => '',
            'both'       => '',
            'reversed'   => '',
            'none'       => ''
        ];
    }

    /**
     * Shortcut for setRouteParts and getRouteParts.
     *
     * @param string $route_path
     * @return array
     */
    public function createRouteParts(string $route_path = ''): array
    {
        $this->setRouteParts($route_path);
        return $this->getRouteParts();
    }

    /**
     * Returns the routes database record based on the request uri.
     * Note that this is a recursive method so it can find a route which is
     * a subset of the request uri, e.g. /fred/flinstone/barney/rubble/
     * could return the route for /fred/flinstone/ if there is no
     * /fred/flinstone/barney/rubble/ request_uri based route.
     *
     * @param string $request_uri
     * @return array
     */
    public function findValidRoute(string $request_uri = ''): array
    {
        $cache_key = 'route.valid.by.request_uri.';
        $cache_key .= Strings::uriToCache($request_uri);
        if ($this->use_cache) {
            /** @var array $valid_route */
            $valid_route = $this->o_cache->get($cache_key);
            if (!empty($valid_route)) {
                return $valid_route;
            }
        }
        $o_routes     = new RoutesComplexModel($this->o_di);
        $a_search_for = $this->createComparisonUri($request_uri);
        foreach ($a_search_for as $value) {
            try {
                $a_results = $o_routes->readByRequestUri($value);
                if (!empty($a_results[0])) {
                    if ($this->use_cache) {
                        $this->o_cache->set($cache_key, $a_results[0], 'route');
                    }
                    return $a_results[0];
                }
            }
            catch (ModelException) {
                if ($request_uri === '/') {
                    return [];
                }
            }
        }

        /* Well, looks like the uri doesn't exist as is so check to see if a part of the uri is a route */
        $uri         = $a_search_for['none'];
        $a_uri_parts = explode('/', $uri);
        array_pop($a_uri_parts);
        $new_request_uri = '/' . implode('/', $a_uri_parts) . '/';
        if (strrpos($new_request_uri, '//') !== false) {
            $new_request_uri = substr($new_request_uri, 0, -1);
        }
        return $this->findValidRoute($new_request_uri);
    }

    /**
     * Prepares the request uri to be explodes to establish uri actions.
     * Uri actions are any parts of the uri that are not part of the route path
     * so the route path has to be removed from the request uri string. Also remove
     * any starting slashes and ending slashes.
     * For example: $request_uri = '/test/fred/barney/' and $route_path is '/test/'
     *              then the returned string = 'fred/barney'
     *
     * @param string $request_uri required.
     * @param string $route_path  required.
     * @return string
     */
    private function prepareToExplode(string $request_uri = '', string $route_path = ''): string
    {
        $meth = __METHOD__ . '.';
        if ($request_uri === '/' || $request_uri === '' || $route_path === '') {
            return '';
        }

        if ($this->compareUriToRoute($request_uri, $route_path)) {
            return '';
        }

        $route_path = Strings::trimSlashes($route_path);
        $this->logIt("Request: {$request_uri} and Route: {$route_path}", LOG_OFF, $meth . __LINE__);

        if ($route_path !== '/' && $route_path !== '') {
            $request_uri = str_replace($route_path, '', $request_uri);
        }

        return Strings::trimSlashes($request_uri);
    }

    /**
     * Gets the groups for a given route.
     *
     * @param int $route_id
     * @return array
     */
    public function getGroups(int $route_id = -1): array
    {
        if ($route_id === -1) {
            return [];
        }
        $cache_key = 'groups.for.route.' . $route_id;
        if ($this->use_cache) {
            $a_groups_json = $this->o_cache->get($cache_key);
            if (!empty($a_groups_json)) {
                return json_decode($a_groups_json, true, 512, JSON_PARTIAL_OUTPUT_ON_ERROR);
            }
        }
        $o_rgm = new RoutesGroupMapModel($this->o_db);
        $o_rgm->setElog($this->o_elog);
        try {
            $a_rgm_results = $o_rgm->read(['route_id' => $route_id]);
            if (!empty($a_rgm_results)) {
                $a_groups = [];
                foreach ($a_rgm_results as $a_rgm) {
                    $a_groups[] = $a_rgm['group_id'];
                }
                if ($this->use_cache) {
                    $json = json_encode($a_groups, JSON_PARTIAL_OUTPUT_ON_ERROR);
                    $this->o_cache->set($cache_key, $json, 'groups');
                }
                return $a_groups;
            }
            return [];
        }
        catch (ModelException) {
            return [];
        }
    }

    /**
     * Gets the min auth level needed for the route.
     * The groups for the route are passed in
     * and iterated searching the group db for the auth level.
     *
     * @param array $a_groups
     * @return int
     */
    public function getMinAuthLevel(array $a_groups = []): int
    {
        $meth = __METHOD__ . '.';
        $hash = md5(json_encode($a_groups, JSON_PARTIAL_OUTPUT_ON_ERROR));
        $key  = 'route.minauthlevel.' . $hash;
        if ($this->use_cache) {
            $results = $this->o_cache->get($key);
            if (!empty($results)) {
                return $results;
            }
        }
        $min_auth_level = 0;
        $o_group        = new GroupsModel($this->o_db);
        $pi_name        = $o_group->getPrimaryIndexName();
        foreach ($a_groups as $group_id) {
            try {
                $results = $o_group->readById($group_id, $pi_name);
                if (!empty($results['group_auth_level'])) {
                    $the_auth_level = $results['group_auth_level'];
                    if ($the_auth_level < $min_auth_level) {
                        $min_auth_level = $the_auth_level;
                    }
                }
            }
            catch (ModelException $e) {
                $this->logIt('DB problem: ' . $e->errorMessage(), LOG_ALWAYS, $meth . __LINE__);
            }
        }
        if ($this->use_cache) {
            $this->o_cache->set($key, $min_auth_level, 'route');
        }
        return $min_auth_level;
    }

    /**
     * Returns minimum auth level for a route.
     *
     * @param int $route_id if not supplied an auth level of 10 is returned.
     * @return int
     */
    public function getMinAuthLevelForRoute(int $route_id = -1): int
    {
        if ($route_id === -1) {
            return 10;
        }
        $a_groups = $this->getGroups($route_id);
        return $this->getMinAuthLevel($a_groups);
    }

    /**
     * Standard GETter for class property route_path.
     *
     * @return string
     */
    public function getRoutePath(): string
    {
        return $this->route_path;
    }

    /**
     * Standard GETter for class property a_route_parts.
     *
     * @return array
     */
    public function getRouteParts(): array
    {
        return $this->a_route_parts;
    }

    /**
     * Standard GETter for class property request_uri.
     *
     * @return string
     */
    public function getRequestUri(): string
    {
        return $this->request_uri;
    }

    /**
     * Sets the class property a_route_parts.
     * a_route_parts provides a lot of information regarding the route/page to be displayed.
     *
     * @param string $request_uri
     */
    public function setRouteParts(string $request_uri = ''): void
    {
        $meth          = __METHOD__ . '.';
        $a_route_parts = [
            'route_id'       => 0,
            'route_path'     => $request_uri,
            'request_uri'    => $request_uri,
            'route_class'    => 'MainController',
            'route_method'   => '',
            'route_action'   => '',
            'url_id'         => -1,
            'url_actions'    => [],
            'groups'         => [],
            'min_auth_level' => 0
        ];
        if ($request_uri === '') {
            $request_uri = $this->request_uri !== ''
                ? $this->request_uri
                : $_SERVER['REQUEST_URI'];
        }
        $cache_key = 'route.parts.for.';
        $cache_key .= $request_uri !== '/'
            ? Strings::uriToCache($request_uri)
            : 'home';
        if ($this->use_cache) {
            $a_route_parts = $this->o_cache->get($cache_key);
            if (!empty($a_route_parts)) {
                $this->a_route_parts = $a_route_parts;
            }
        }

        $a_route = $this->findValidRoute($request_uri);
        if (!empty($a_route)) {
            $a_route_parts = $a_route;
        }
        $log_message = 'Route Found:  ' . var_export($a_route, true);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        if ($a_route_parts['route_id'] !== 0) {
            $this->request_uri            = $request_uri;
            $this->route_path             = $a_route_parts['url_text'];
            $a_route_parts['request_uri'] = $request_uri;
            $a_route_parts['route_path']  = $a_route_parts['url_text'];
            $a_url_actions = [];
            if ($this->compareUriToRoute($this->request_uri, $this->route_path) === false) {
                $uri_actions = $this->prepareToExplode($this->request_uri, $this->route_path);
                $this->logIt("URI Actions string: {$uri_actions}", LOG_OFF, $meth . __LINE__);
                $a_url_actions = explode('/', $uri_actions);
            }

            $a_route_parts['url_actions']    = $a_url_actions;
            $a_route_parts['groups']         = $this->getGroups($a_route_parts['route_id']);
            $a_route_parts['min_auth_level'] = $this->getMinAuthLevel($a_route_parts['groups']);
        }

        $log_message = 'Route parts:  ' . var_export($a_route_parts, true);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
        if ($this->use_cache) {
            $this->o_cache->set($cache_key, $a_route_parts, 'route');
        }
        $this->a_route_parts = $a_route_parts;
    }

    /**
     * Sets the class property route_path.
     *
     * @param string $route_path
     */
    public function setRoutePath(string $route_path = ''): void
    {
        $this->route_path = $route_path;
    }

    /**
     * @return string
     */
    public function getCacheType(): string
    {
        return $this->cache_type;
    }

    /**
     * @param string $cache_type
     */
    public function setCacheType(string $cache_type): void
    {
        $this->cache_type = $cache_type;
    }

    /**
     * @return CacheHelperSymfony
     */
    public function getCache(): CacheHelperSymfony
    {
        return $this->o_cache;
    }

    /**
     * @param CacheHelperSymfony $o_cache
     */
    public function setCache(CacheHelperSymfony $o_cache): void
    {
        $this->o_cache = $o_cache;
    }

    /**
     * @return bool
     */
    public function isUseCache(): bool
    {
        return $this->use_cache;
    }

    /**
     * @param bool $use_cache
     */
    public function setUseCache(bool $use_cache): void
    {
        $this->use_cache = $use_cache;
    }

}
