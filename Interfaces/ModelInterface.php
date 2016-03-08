<?php
/**
 *  @brief     Class used to set up model classes.
 *  @ingroup   ritc_library lib_interface
 *  @file      Ritc/Library/Interfaces/ModelInterface.php
 *  @namespace Ritc\Library\Interfaces
 *  @class     ModelInterface
 *  @author    William E Reveal <bill@revealitconsulting.com>
 *  @version   1.0.1
 *  @date      2014-11-11 11:14:58
 *  @note <pre><b>Change Log</b>
 *      v1.0.1 fixed default arg for delete
 *      v1.0.0 initial versioning 01/30/2014 wer
 *  </pre>
**/
namespace Ritc\Library\Interfaces;

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

    /**
     * Returns the SQL error message
     * @return string
     */
    public function getErrorMessage();
}
