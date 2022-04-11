<?php
/**
 * Interface ModelInterface
 * @package Ritc_Library
 */
namespace Ritc\Library\Interfaces;

use Ritc\Library\Exceptions\ModelException;

/**
 * Interface ModelInterface
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 2.0.0
 * @date    2021-11-29 17:14:57
 * @change_log
 * - v2.0.0 updated for php8                                                    - 2021-11-29 wer
 * - v1.4.0 Added new param to create                                           - 2018-10-24 wer
 * - v1.3.0 Added throws phpDoc line to remind one to use it                    - 2018-04-26 wer
 * - v1.2.0 Added new param to read, one that is used almost always anyway      - 2016-03-24 wer
 * - v1.1.0 moved getErrorMessage from interface to the DbUtilityTraits class   - 2016-03-19 wer
 * - v1.0.1 fixed default arg for delete                                        - 11/11/2014 wer
 * - v1.0.0 initial version                                                     - 01/30/2014 wer
 */
interface ModelInterface {
    /**
     * Create a record using the values provided.
     *
     * @param array $a_values Required
     * @param bool  $allow_pin
     * @return array
     */
    public function create(array $a_values, bool $allow_pin):array;

    /**
     * Returns an array of records based on the search params provided.
     *
     * @param array $a_search_for    key pairs of field name => field value
     * @param array $a_search_params \ref searchparams \ref readparams
     * @return array
     * @throws ModelException
     */
    public function read(array $a_search_for, array $a_search_params):array;

    /**
     * Update for a record using the values provided.
     *
     * @param array $a_values Required
     * @return bool
     * @throws ModelException
     */
    public function update(array $a_values):bool;

    /**
     * Deletes a record based on the id provided.
     *
     * @param int $id Required
     * @return bool
     * @throws ModelException
     */
    public function delete(int $id):bool;
}
