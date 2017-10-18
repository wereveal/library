<?php
/**
 * @brief     Interface used to set up model classes.
 * @ingroup   lib_interfaces
 * @file      Ritc/Library/Interfaces/ModelInterface.php
 * @namespace Ritc\Library\Interfaces
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.2.0+2
 * @date      2016-04-10 14:27:28
 * @note <b>Change Log</b>
 * - v1.2.0 Added new param to read, one that is used almost always anyway                      - 2016-03-24 wer
 * - v1.1.0 moved getErrorMessage from interface to the DbUtilityTraits class                   - 2016-03-19 wer
 * - v1.0.1 fixed default arg for delete                                                        - 11/11/2014 wer
 * - v1.0.0 initial versioning                                                                  - 01/30/2014 wer
 */
namespace Ritc\Library\Interfaces;

/**
 * Interface ModelInterface
 * @class ModelInterface
 * @package Ritc\Library\Interfaces
 */
interface ModelInterface {
    /**
     * Create a record using the values provided.
     * @param array $a_values
     * @return bool
     */
    public function create(array $a_values);

    /**
     * Returns an array of records based on the search params provided.
     * @param array $a_search_for    key pairs of field name => field value
     * @param array $a_search_params \ref searchparams \ref readparams
     * @return array
     */
    public function read(array $a_search_for, array $a_search_params);

    /**
     * Update for a record using the values provided.
     * @param array $a_values
     * @return bool
     */
    public function update(array $a_values);

    /**
     * Deletes a record based on the id provided.
     * @param string $id
     * @return bool
     */
    public function delete($id);
}
