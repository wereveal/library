<?php
/**
 *  @brief Class used to set up classes that look to establish the location of a file.
 *  @file LocationInterface.php
 *  @ingroup ritc_library core
 *  @namespace Ritc/Library/Interfaces
 *  @class LocationInterface
 *  @author William Reveal <bill@revealitconsulting.com>
 *  @version 2.0.0
 *  @date 2011-06-14 15:11:26
 *  @note A part of the RITC Library v5
 *  @note <pre><b>Change Log</b>
 *      v2.0.0 Changed to an interface 12/19/2013 wer
 *      v1.1.0 06/14/2011 wer
 *  </pre>
**/
namespace Ritc\Library\Interfaces;

interface LocationInterface
{
    /**
     *  Getters and Setters
     *  Looking to set two class properties
     *  $file_name and $file_dir_name
    **/
    public function getFileDirName();
    public function getFileName();
    public function setFileDirName($value);
    public function setFileName($value);
}