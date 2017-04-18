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
use Ritc\Library\Traits\LogitTraits;

/**
 * Class CustomException.
 * @class   CustomException
 * @package Ritc\Library\Abstracts
 */
abstract class CustomException extends \Exception implements CustomExceptionInterface
{
    use LogitTraits;

    public function __construct($message = '', $code = 0)
    {
        if ($message == '') {
            throw new $this('No Message Provided for ' . get_class($this));
        }
        parent::__construct($message, $code);
    }

    public function __toString()
    {
        return get_class($this) .
        " '{$this->message}' in {$this->file}.{$this->line}\n" .
        $this->getTraceAsString();
    }

}