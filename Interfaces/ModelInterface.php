<?php
namespace Ritc\Library\Interfaces;

/**
 * Interface ModelInterface
 *
 * @package RITC_Library
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.3.0
 * @date    2018-04-26 06:02:16
 * ## Change Log
 * - v1.3.0 Added throws phpDoc line to remind one to use it                                    - 2018-04-26 wer
 * - v1.2.0 Added new param to read, one that is used almost always anyway                      - 2016-03-24 wer
 * - v1.1.0 moved getErrorMessage from interface to the DbUtilityTraits class                   - 2016-03-19 wer
 * - v1.0.1 fixed default arg for delete                                                        - 11/11/2014 wer
 * - v1.0.0 initial versioning                                                                  - 01/30/2014 wer
 */
interface ModelInterface {
    /**
     * Create a record using the values provided.
     * @param array $a_values Required
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function create(array $a_values);

    /**
     * Returns an array of records based on the search params provided.
     * @param array $a_search_for    key pairs of field name => field value
     * @param array $a_search_params \ref searchparams \ref readparams
     * @return array
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function read(array $a_search_for, array $a_search_params);

    /**
     * Update for a record using the values provided.
     * @param array $a_values Required
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function update(array $a_values);

    /**
     * Deletes a record based on the id provided.
     * @param int $id Required
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function delete($id);
}
