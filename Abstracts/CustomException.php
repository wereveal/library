<?php
/**
 * @brief     Abstract which extends php Exception class.
 * @ingroup   lib_abstracts
 * @file      Ritc/Library/Abstracts/CustomException.php
 * @namespace Ritc\Library\Abstracts
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.0
 * @date      2016-10-17 11:24:10
 * @note Change Log
 * - v1.0.0-alpha.0 - Initial version                                                                   - 2016-10-17 wer
 */
namespace Ritc\Library\Abstracts;

use Ritc\Library\Interfaces\CustomExceptionInterface;

/**
 * Class CustomException.
 * @class   CustomException
 * @package Ritc\Library\Abstracts
 */
abstract class CustomException extends \Exception implements CustomExceptionInterface
{
    public function __toString()
    {
        return get_class($this) .
        " '{$this->message}' in {$this->file}.{$this->line}\n" .
        $this->getTraceAsString();
    }

    public function errorMessage()
    {
        $error_message = $this->getMessage() . ' -- ' . $this->getFile() . '.' . $this->getLine();
        return $error_message;
    }

    public function getCodeText($code = -1)
    {
        switch ($code) {
            default:
                return 'Unspecified error';
        }
    }

    public function getClass()
    {
        return get_class($this);
    }
}