<?php
/**
 * @brief     Does multi-table operations.
 * @details   Routes, groups, and urls may be used..
 * @ingroup   lib_models
 * @file      Ritc/Library/Models/RoutesComplexModel.php
 * @namespace Ritc\Library\Models
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.0
 * @date      2017-06-18 14:23:31
 * @note Change Log
 * - v1.0.0-alpha.0 - Initial version        - 2017-06-18 wer
 * @todo Ritc/Library/Models/RoutesComplexModel.php - Everything
 */
namespace Ritc\Library\Models;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\DbUtilityTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class RoutesComplexModel.
 * @class   RoutesComplexModel
 * @package Ritc\Library\Models
 */
class RoutesComplexModel
{
    use LogitTraits, DbUtilityTraits;

    public function __construct(Di $o_di)
    {
        $o_db = $o_di->get('db');
        $this->setupElog($o_di);
        $this->setupProperties($o_db);
    }

    /**
     * Reads the route with the request uri.
     * @param string $request_uri normally obtained from $_SERVER['REQUEST_URI']
     * @return mixed
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function readByRequestUri($request_uri = '')
    {
        if ($request_uri == '') {
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
     * Returns the list of all the routes with the url.
     * A join between routes and urls tables.
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
        $url_fields   = $o_url->getDbFields();
        $route_fields = $this->buildSqlSelectFields($route_fields, 'r');
        $url_fields   = $this->buildSqlSelectFields($url_fields, 'u');
        $sql = "
            SELECT {$route_fields},
                   {$url_fields}
            FROM {$this->lib_prefix}routes as r, {$this->lib_prefix}urls as u
            WHERE r.url_id = u.url_id
            ORDER BY r.route_immutable DESC, u.url_text
        ";
        try {
            return $this->o_db->search($sql);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }
}