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
 * @version 2.0.0
 * @date    2021-11-29 17:13:13
 * @change_log
 * - v2.0.0 - updated for php8                                  - 2021-11-29 wer
 * - v1.0.0 - Initial Version                                   - 2011-06-14 wer
 */
interface LocationInterface
{
    /**
     * Returns the file directory name.
     *
     * @return string
     */
    public function getFileDirName(): string;

    /**
     * Returns the file name.
     *
     * @return string
     */
    public function getFileName(): string;

    /**
     * Sets the file directory name.
     *
     * @param $value
     */
    public function setFileDirName($value): void;

    /**
     * Sets the file name.
     *
     * @param $value
     */
    public function setFileName($value): void;
}
