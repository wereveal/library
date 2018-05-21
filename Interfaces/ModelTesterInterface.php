<?php
/**
 * Interface ModelTesterInterface
 * @package Ritc_Library
 */
namespace Ritc\Library\Interfaces;

/**
 * Interface ModelTesterInterface
 * Model classes should be implementing the ModelInterface to match.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0
 * @date    2017-10-18 12:10:55
 * ## Change Log
 * - v1.0.0 initial version                                  - 2017-10-18 wer
 */
interface ModelTesterInterface {
    /**
     * Tests the create method.
     * @return string
     */
    public function createTester();

    /**
     * Tests the read method.
     * @return string
     */
    public function readTester();

    /**
     * Tests the update method.
     * @return string
     */
    public function updateTester();

    /**
     * Tests the delete method.
     * @return string
     */
    public function deleteTester();
}
