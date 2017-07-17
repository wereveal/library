<?php
/**
 * @brief     Exceptions specific to database, application rules and business logic operations.
 * @ingroup   lib_exceptions
 * @file      Ritc/Library/Exceptions/CustomErrorException.php
 * @namespace Ritc\Library\Exceptions
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.0
 * @date      2017-07-15 12:41:34
 * @note Change Log
 * - v1.0.0-alpha.0 - Initial version        - 2017-07-15 wer
 */
namespace Ritc\Library\Exceptions;

use Ritc\Library\Abstracts\CustomError;

/**
 * Class CustomErrorException.
 * @class   CustomErrorException
 * @package Ritc\Library\Basic
 */
class CustomErrorException extends CustomError
{
    public function __construct($message = "", $code = 0, $severity = 1, $filename = __FILE__, $lineno = __LINE__, \Exception $previous)
    {
        parent::__construct($message, $code, $severity, $filename, $lineno, $previous);
    }

    /**
     * @param int $code
     * @return string
     */
    public function getCodeText($code = -1)
    {
        switch ($code) {
            default:
                return parent::getCodeText($code);

        }
    }

}
