<?php
/**
 *  Interface for database CRUD.
**/
namespace Ritc\Blog\Models;

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
     * @param string $id
     * @param array $a_values
     * @return bool
     */
    public function update($id = '', array $a_values);

    /**
     * Generic deletes a record based on the id provided.
     * @param string $id
     * @return bool
     */
    public function delete($id = '');
}