<?php
/**
 * @brief     Class used to set up model classes.
 * @ingroup   lib_interfaces
 * @file      Ritc/Library/Interfaces/ModelInterface.php
 * @namespace Ritc\Library\Interfaces
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.1.0
 * @date      2016-03-19 06:26:14
 * @note <b>Change Log</b>
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
     * Generic create a record using the values provided.
     * @param array $a_values
     * @return bool
     */
    public function create(array $a_values);

    /**
     * Returns an array of records based on the search params provided.
     * @param array $a_search_values
     * @return array
     */
    public function read(array $a_search_values);

    /**
     * Generic update for a record using the values provided.
     * @param array $a_values
     * @return bool
     */
    public function update(array $a_values);

    /**
     * Generic deletes a record based on the id provided.
     * @param int $id
     * @return bool
     */
    public function delete($id = -1);
}
