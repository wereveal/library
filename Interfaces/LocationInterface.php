<?php
/**
 * @brief     Class used to set up classes that look to establish the location of a file.
 * @ingroup   lib_interfaces
 * @file      Ritc/Library/Interfaces/LocationInterface.php
 * @namespace Ritc\Library\Interfaces
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   2.0.0
 * @date      2011-06-14 15:11:26
 * @note <b>Change Log</b>
 * - v2.0.0 Changed to an interface 12/19/2013 wer
 * - v1.1.0 06/14/2011 wer
 */
namespace Ritc\Library\Interfaces;

/**
 * Interface LocationInterface.
 * @class LocationInterface
 * @package Ritc\Library\Interfaces
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
