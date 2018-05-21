<?php
/**
 * Interface LocationInterface
 * @package Ritc_Library
 */
namespace Ritc\Library\Interfaces;

/**
 * Interface for location based helpers.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0
 * @date    2011-06-14 15:11:26
 * ## Change Log
 * - v1.0.0 Initial Version             - 2011-06-14 wer
 */
interface LocationInterface
{
    /**
     * Returns the file directory name.
     * @return mixed
     */
    public function getFileDirName();

    /**
     * Returns the file name.
     * @return mixed
     */
    public function getFileName();

    /**
     * Sets the file directory name.
     * @param $value
     * @return mixed
     */
    public function setFileDirName($value);

    /**
     * Sets the file name.
     * @param $value
     * @return mixed
     */
    public function setFileName($value);
}
