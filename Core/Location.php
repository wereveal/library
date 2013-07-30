<?php
/**
 *  Class used to set up classes that look to establish the location of a file.
 *  @file Location.php
 *  @namespace Ritc\Library\Core
 *  @class Location
 *  @author William Reveal <bill@revealitconsulting.com>
 *  @version 1.1.0
 *  @date 2011-06-14 15:11:26
 *  @ingroup ritc_library library
 *  @par RITC Library 4.0
**/
namespace Ritc\Library\Core;

use Ritc\Library\Abstract\Base;

class Location extends Base
{
    protected $file_name;
    protected $file_dir_name;

    public function setFileName($value)
    {
        $this->file_name = $value;
        return true;
    }
    public function setFileDirName($value)
    {
        $this->file_dir_name = $value;
        return true;
    }
    public function getFileName()
    {
        return $this->file_name;
    }
    public function getFileDirName()
    {
        return $this->file_dir_name;
    }
}
