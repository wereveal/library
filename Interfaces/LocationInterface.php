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
 * @version v2.0.0
 * @date    2021-11-29 17:13:13
 * @change_log
 * - v2.0.0 - updated for php8                                  - 2021-11-29 wer
 * - v1.0.0 - Initial Version                                   - 2011-06-14 wer
 */
interface LocationInterface
{
    /**
     * Returns the file directory name.
     * @return mixed
     */
    public function getFileDirName(): mixed;

    /**
     * Returns the file name.
     * @return mixed
     */
    public function getFileName(): mixed;

    /**
     * Sets the file directory name.
     * @param $value
     * @return mixed
     */
    public function setFileDirName($value): mixed;

    /**
     * Sets the file name.
     * @param $value
     * @return mixed
     */
    public function setFileName($value): mixed;
}
