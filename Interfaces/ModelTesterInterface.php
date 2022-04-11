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
 * @version 1.0.0
 * @date    2017-10-18 12:10:55
 * @change_log
 * - v1.0.0 initial version                                  - 2017-10-18 wer
 */
interface ModelTesterInterface {
    /**
     * Tests the create method.
     * @return string
     */
    public function createTester():string;

    /**
     * Tests the read method.
     * @return string
     */
    public function readTester():string;

    /**
     * Tests the update method.
     * @return string
     */
    public function updateTester():string;

    /**
     * Tests the delete method.
     * @return string
     */
    public function deleteTester():string;
}
