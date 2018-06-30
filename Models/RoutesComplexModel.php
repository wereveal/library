<?php
/**
 * Class RoutesComplexModel
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Helper\Strings;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\DbUtilityTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Does all the Model expected operations, database CRUD and business logic.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-alpha.0
 * @date    2017-06-18 14:23:31
 * @change_log
 * - v1.0.0-alpha.0 - Initial version        - 2017-06-18 wer
 */
class RoutesComplexModel
{
    use LogitTraits, DbUtilityTraits;

    /** @var Di $o_di */
    private $o_di;

    /**
     * RoutesComplexModel constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->o_di = $o_di;
        /** @var \Ritc\Library\Services\DbModel $o_db */
        $o_db = $o_di->get('db');
        $this->setupElog($o_di);
        $this->setupProperties($o_db);
    }

    /**
     * Deletes a route if not immutable with map to group(s).
     *
     * @param int $route_id
     * @return bool
     * @throws ModelException
     */
    public function delete(int $route_id = -1):bool
    {
        if ($route_id < 1) {
            $err_code = ExceptionHelper::getCodeNumberModel('delete missing primary');
            throw new ModelException('Missing route id.', $err_code);
        }
        $o_route = new RoutesModel($this->o_db);
        $o_route->setupElog($this->o_di);
        if ($o_route->isImmutable($route_id)) {
            $err_code = ExceptionHelper::getCodeNumberModel('delete immutable');
            throw new ModelException('Route immutable.', $err_code);
        }
        $o_rgm = new RoutesGroupMapModel($this->o_db);
        $o_rgm->setupElog($this->o_di);
        try {
            $this->o_db->startTransaction();
            $o_rgm->deleteByRouteId($route_id);
            $o_route->delete($route_id);
            $this->o_db->commitTransaction();
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
        return true;
    }

    /**
     * Returns the route(s) with the url and group(s).
     * A join between routes and urls tables then add results of a join
     * between routes group map and groups table to result(s).
     *
     * @param int $route_id Optional, if omitted, all routes are returned.
     * @return array
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function readAll(int $route_id = -1):array
    {
        $o_url = new UrlsModel($this->o_db);
        $o_route = new RoutesModel($this->o_db);
        $o_url->setupElog($this->o_di);
        $o_route->setupElog($this->o_di);
        $route_fields = $o_route->getDbFields();
        unset($route_fields['url_id']);
        $url_fields   = $o_url->getDbFields();
        $route_fields = $this->buildSqlSelectFields($route_fields, 'r');
        $url_fields   = $this->buildSqlSelectFields($url_fields, 'u');
        $a_search_for = [];
        $where = '';
        $sql = "
            SELECT {$route_fields},
                   {$url_fields}
            FROM {$this->lib_prefix}routes as r
            JOIN {$this->lib_prefix}urls as u
              ON r.url_id = u.url_id";
        if ($route_id > 0) {
            $where = '
            WHERE r.route_id = :route_id';
            $a_search_for = [':route_id' => $route_id];
        }
        $order_by = '
            ORDER BY r.route_immutable DESC, u.url_text';
        $sql .= $where . $order_by;
        try {
            $a_routes = $this->o_db->search($sql, $a_search_for);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
        $o_groups = new GroupsModel($this->o_db);
        $o_groups->setupElog($this->o_di);
        $group_fields = $o_groups->getDbFields();
        $group_fields = $this->buildSqlSelectFields($group_fields, 'g');
        $sql = "
            SELECT {$group_fields}
            FROM {$this->lib_prefix}routes_group_map as rgm
            JOIN {$this->lib_prefix}groups as g
              ON rgm.group_id = g.group_id
            WHERE rgm.route_id = :route_id
        ";
        try {
            $o_stmt = $this->o_db->prepare($sql);
        }
        catch (ModelException $e) {
            $err_code = ExceptionHelper::getCodeNumberModel('prepare');
            throw new ModelException('Unable to prepare to get the groups.', $err_code, $e);
        }
        foreach ($a_routes as $key => $route) {
            $a_search_for = [':route_id' => $route['route_id']];
            try {
                $this->o_db->execute($a_search_for, $o_stmt);
                $a_groups = $this->o_db->fetchAll($o_stmt);
            }
            catch (ModelException $e) {
                $err_code = ExceptionHelper::getCodeNumberModel('read unknown');
                throw new ModelException('Unable to prepare to get the groups.', $err_code, $e);
            }
            $a_routes[$key]['groups'] = $a_groups;
        }
        return $a_routes;
    }

    /**
     * Returns the list of all the routes with the url.
     * A join between routes and urls tables.
     *
     * @return array|bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function readAllWithUrl()
    {
        $o_url = new UrlsModel($this->o_db);
        $o_route = new RoutesModel($this->o_db);
        $o_url->setElog($this->o_elog);
        $o_route->setElog($this->o_elog);
        $route_fields = $o_route->getDbFields();
        unset($route_fields['url_id']);
        $url_fields   = $o_url->getDbFields();
        $route_fields = $this->buildSqlSelectFields($route_fields, 'r');
        $url_fields   = $this->buildSqlSelectFields($url_fields, 'u');
        $sql = "
            SELECT {$route_fields},
                   {$url_fields}
            FROM {$this->lib_prefix}routes as r
            JOIN {$this->lib_prefix}urls as u
              ON r.url_id = u.url_id
            ORDER BY r.route_immutable DESC, u.url_text
        ";
        try {
            return $this->o_db->search($sql);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Reads the route with the request uri.
     * @param string $request_uri normally obtained from $_SERVER['REQUEST_URI']
     * @return mixed
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function readByRequestUri(string $request_uri = '')
    {
        if ($request_uri === '') {
            throw new ModelException('Missing required value: request uri', 220);
        }
        $a_search_params = [':url_text' => $request_uri];
        $o_url = new UrlsModel($this->o_db);
        $o_route = new RoutesModel($this->o_db);
        $o_url->setElog($this->o_elog);
        $o_route->setElog($this->o_elog);
        $route_fields = $o_route->getDbFields();
        $url_fields   = $o_url->getDbFields();
        $route_fields = $this->buildSqlSelectFields($route_fields, 'r');
        $url_fields   = $this->buildSqlSelectFields($url_fields, 'u');
        $sql = "
            SELECT {$route_fields},
                   {$url_fields}
            FROM {$this->lib_prefix}routes as r, {$this->lib_prefix}urls as u
            WHERE r.url_id = u.url_id
            AND u.url_text = :url_text
            ORDER BY u.url_text
        ";
        try {
            return $this->o_db->search($sql, $a_search_params);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Reads the groups mapped to the route id.
     *
     * @param int $route_id
     * @return array
     * @throws ModelException
     */
    public function readGroupsFor(int $route_id = -1):?array
    {
        if ($route_id < 1) {
            $err_code = ExceptionHelper::getCodeNumberModel('read missing value');
            throw new ModelException('Missing Route ID.', $err_code);
        }
        $sql = "
            SELECT g.*
            FROM {$this->lib_prefix}routes_group_map as rgm
            JOIN {$this->lib_prefix}groups as g
              ON rgm.group_id = g.group_id
            WHERE rgm.route_id = :route_id
        ";
        try {
            return $this->o_db->search($sql, [':route_id' => $route_id]);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Reads a route record with associated url info.
     *
     * @param int $route_id
     * @return bool
     * @throws ModelException
     */
    public function readWithUrl(int $route_id = -1):?bool
    {
        if ($route_id < 1) {
            $err_code = ExceptionHelper::getCodeNumberModel('read missing value');
            throw new ModelException('Missing route id.', $err_code);
        }
        $o_url = new UrlsModel($this->o_db);
        $o_route = new RoutesModel($this->o_db);
        $o_url->setElog($this->o_elog);
        $o_route->setElog($this->o_elog);
        $route_fields = $o_route->getDbFields();
        unset($route_fields['url_id']);
        $url_fields   = $o_url->getDbFields();
        $route_fields = $this->buildSqlSelectFields($route_fields, 'r');
        $url_fields   = $this->buildSqlSelectFields($url_fields, 'u');
        $sql = "
            SELECT {$route_fields},
                   {$url_fields}
            FROM {$this->lib_prefix}routes as r
            JOIN {$this->lib_prefix}urls as u
              ON r.url_id = u.url_id
            WHERE r.route_id = :route_id
            ORDER BY r.route_immutable DESC, u.url_text
        ";
        $a_search_for = [':route_id' => $route_id];
        try {
            return $this->o_db->search($sql, $a_search_for);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Saves a new route with map to group.
     * Data comes normally from form.
     *
     * @param array $a_from_post Required
     * @return bool
     * @throws ModelException
     */
    public function saveNew(array $a_from_post = []):bool
    {
        $meth = __METHOD__ . '.';
        $a_route = $this->fixRoute($a_from_post['route']);
        if ($a_route === false) {
            $message = 'A Problem Has Occured. Required values missing.';
            $err_code = ExceptionHelper::getCodeNumberModel('create missing values');
            throw new ModelException($message, $err_code);
        }

        $a_groups = $this->fixGroups($a_from_post['group'], -1);
        if ($a_groups === false) {
            $message = 'A Problem Has Occured. Required group values missing.';
            $err_code = ExceptionHelper::getCodeNumberModel('create missing values');
            throw new ModelException($message, $err_code);
        }
        $log_message = 'route ' . var_export($a_route, TRUE);
          $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
        $o_routes = new RoutesModel($this->o_db);
        $o_routes->setupElog($this->o_di);
        $o_rgm = new RoutesGroupMapModel($this->o_db);
        $o_rgm->setupElog($this->o_di);
        try {
            $this->o_db->startTransaction();
            $results = $o_routes->create($a_route);
            $route_id = $results[0];
            foreach ($a_groups as $key => $a_group) {
                $a_groups[$key]['route_id'] = $route_id;
            }
              $log_message = 'groups ' . var_export($a_groups, TRUE);
              $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
            $o_rgm->create($a_groups);
            $this->o_db->commitTransaction();
        }
        catch (ModelException $e) {
            $this->o_db->rollbackTransaction();
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
        return true;
    }

    /**
     * Updates an existing route with map to group(s).
     * Data comes normally from form.
     *
     * @param array $a_from_post
     * @return bool
     * @throws ModelException
     */
    public function update(array $a_from_post = []):bool
    {
        $meth = __METHOD__ . '.';
        $a_route = $this->fixRoute($a_from_post['route']);
        $a_groups = [];
        if (empty($a_route['route_id'])) {
            $err_code = ExceptionHelper::getCodeNumberModel('update missing primary');
            throw new ModelException('Missing route id.', $err_code);
        }
        $a_group_ids = $a_from_post['group'];
        try {
            $a_old_groups = $this->readGroupsFor($a_route['route_id']);
        }
        catch (ModelException $e) {
            $err_code = ExceptionHelper::getCodeNumberModel('update unknown');
            throw new ModelException('Unable to get existing groups assigned to the route.', $err_code, $e);
        }
        foreach ($a_old_groups as $old_key => $a_old_group) {
            $key = array_search($a_old_group['group_id'], $a_group_ids);
            if ($key) {
                unset($a_group_ids[$key], $a_old_groups[$old_key]); // already has a record
                // no need to delete map record
            }
        }
        if (!empty($a_group_ids)) { // we have new map records to create
            $a_groups = $this->fixGroups($a_group_ids, $a_route['route_id']);
        }
        $log_message = 'groups ' . var_export($a_groups, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        $o_rgm = new RoutesGroupMapModel($this->o_db);
        $o_routes = new RoutesModel($this->o_db);
        $o_rgm->setupElog($this->o_di);
        $o_routes->setupElog($this->o_di);
        try {
            $this->o_db->startTransaction();
            if (!empty($a_old_groups)) { // need to delete unassigned
                foreach ($a_old_groups as $group) {
                    $o_rgm->deleteByRouteGroup($a_route['route_id'], $group['group_id']);
                }
            }
            if (!empty($a_group_ids)) {
                $o_rgm->create($a_groups);
            }
            $o_routes->update($a_route);
            $this->o_db->commitTransaction();
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
        return true;
    }

    ### Utilities ###

    /**
     * Prepares the group array for use in create and update methods.
     *
     * @param array $a_groups
     * @param       $route_id
     * @return array
     */
    private function fixGroups(array $a_groups = [], $route_id):array
    {
        $a_new_groups = [];
        foreach ($a_groups as $group_id => $value) {
            $a_new_groups[] = [
                'group_id' => $group_id,
                'route_id' => $route_id
            ];
        }
        return $a_new_groups;
    }

    /**
     * Fixes values to be valid for save/updates.
     *
     * @param array $a_route Required ['url_id','route_class','route_method'].
     * @return array|bool
     */
    private function fixRoute(array $a_route = [])
    {
        if (empty($a_route) ||
            empty($a_route['url_id']) ||
            empty($a_route['route_class']) ||
            empty($a_route['route_method'])
        ) {
            return false;
        }
        $a_route['route_action'] = empty($a_route['route_action'])
            ? ''
            : $a_route['route_action'];
        $a_route['route_immutable'] = empty($a_route['route_immutable'])
            ? 'false'
            : $a_route['route_immutable'];
        $a_route['route_class'] = Strings::removeTagsWithDecode($a_route['route_class'], ENT_QUOTES);
        $a_route['route_class'] = Strings::makeCamelCase($a_route['route_class'], false);
        $a_route['route_method'] = Strings::removeTagsWithDecode($a_route['route_method'], ENT_QUOTES);
        $a_route['route_method'] = Strings::makeCamelCase($a_route['route_method'], true);
        return $a_route;
    }
}
