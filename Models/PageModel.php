<?php
/**
 * @brief     Does all the database CRUD stuff for the page table plus other app/business logic.
 * @ingroup   lib_models
 * @file      Ritc/Library/Models/PageModel.php
 * @namespace Ritc\Library\Models
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   2.0.1
 * @date      2017-12-12 11:41:25
 * @note <b>Change Log</b>
 * - v2.0.1   - ModelException changes reflected here                   - 2017-12-12 wer
 * - v2.0.0   - Refactored to use ModelException                        - 2017-06-17 wer
 * - v1.2.2   - DbUtilityTraits change reflected here                   - 2017-05-09 wer
 * - v1.2.1   - refactoring in DbUtiltity traits reflected here         - 2017-01-27 wer
 * - v1.2.0   - refactored to utilize the DbUtilityTraits               - 2016-04-01 wer
 * - v1.1.0   - refactoring changes to DbModel reflected here.          - 2016-03-19 wer
 * - v1.0.0   - take out of beta                                        - 11/27/2015 wer
 * - v1.0.0β1 - Initial version                                         - 10/30/2015 wer
 */
namespace Ritc\Library\Models;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\Arrays;
use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\DbUtilityTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class PageModel.
 * @class   PageModel
 * @package Ritc\Library\Models
 */
class PageModel implements ModelInterface
{
    use LogitTraits, DbUtilityTraits;

    /**
     * PageModel constructor.
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'page');
    }

    /**
     * Create a record using the values provided.
     * @param array $a_values key=>value pairs of url_id=>value and page_title=>value required.
     * @return bool|string
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function create(array $a_values = [])
    {
        if (Arrays::isArrayOfAssocArrays($a_values)) {
            foreach ($a_values as $key => $a_record) {
                if ($this->hasRecords(['url_id' => $a_record['url_id']])) {
                    $this->error_message = "A record already exists using the url id {$a_record['url_id']} for {$a_record['page_title']}";
                    throw new ModelException($this->error_message, 120);
                }
            }
        }
        else {
            if ($this->hasRecords(['url_id' => $a_values['url_id']])) {
                $this->error_message = "A record already exists using the url id {$a_values['url_id']} for {$a_values['page_title']}";
                throw new ModelException($this->error_message, 120);
            }
        }

        $a_parameters = [
            'a_required_keys' => ['url_id', 'page_title'],
            'a_field_names'   => $this->a_db_fields,
            'a_psql'          => [
                'table_name'  => $this->db_table,
                'column_name' => $this->primary_index_name
            ]
        ];

        try {
            $a_results = $this->genericCreate($a_values, $a_parameters);
        }
        catch (ModelException $exception) {
            $this->error_message = $exception->errorMessage();
            $code = $exception->getCode();
            throw new ModelException($this->error_message, $code);
        }
        return $a_results;
    }

    /**
     * Returns an array of records based on the search params provided.
     * @param array $a_search_values optional, defaults to returning all records
     * @param array $a_search_params optional, defaults to ['order_by' => 'route_path'] \ref readparams
     * @return array
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function read(array $a_search_values = array(), array $a_search_params = array())
    {
        $a_parameters = [
            'table_name'     => $this->db_table,
            'a_fields'       => $this->a_db_fields,
            'a_search_for'   => $a_search_values,
            'a_allowed_keys' => $this->a_db_fields
        ];
        $a_parameters = array_merge($a_parameters, $a_search_params);
        if (!isset($a_parameters['order_by'])) {
            $a_parameters['order_by'] = 'page_id';
        }
        try {
            return $this->genericRead($a_parameters);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Generic update for a record using the values provided.
     * @param array $a_values
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function update(array $a_values)
    {
        if (!isset($a_values['page_id'])
            || $a_values['page_id'] == ''
            || (is_string($a_values['page_id']) && !is_numeric($a_values['page_id']))
        ) {
            $this->error_message = 'The Page Id was not supplied.';
            throw new ModelException($this->error_message, 320);
        }
        try {
            return $this->genericUpdate($a_values);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Generic deletes a record based on the id provided.
     * @param int $page_id
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function delete($page_id = -1)
    {
        if ($page_id == -1) {
            throw new ModelException('Missing required page id.', 420);
        }
        try {
            $search_results = $this->read(['page_id' => $page_id], ['a_fields' => ['page_immutable']]);
        }
        catch (ModelException $e) {
            throw new ModelException('Page not available to be deleted.', 410);
        }
        if ($search_results[0]['page_immutable'] == 1) {
            $this->error_message = 'Sorry, that page can not be deleted.';
            throw new ModelException($this->error_message, 450);
        }
        try {
            return $this->genericDelete($page_id);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }
}
