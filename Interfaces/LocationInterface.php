<?php
/**
 *  @brief     Class used to set up classes that look to establish the location of a file.
 *  @ingroup   ritc_library lib_interfaces
 *  @file      LocationInterface.php
 *  @namespace Ritc\Library\Interfaces
 *  @class     LocationInterface
 *  @author    William E Reveal <bill@revealitconsulting.com>
 *  @version   2.0.0
 *  @date      2011-06-14 15:11:26
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
