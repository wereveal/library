<?php
/**
 * @brief     Custom Exceptions for Services.
 * @details   Handles custom exceptions for services.
 * @ingroup   lib_exceptions
 * @file      Ritc/Library/Exceptions/HelperException.php
 * @namespace Ritc\Library\Exceptions
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.0
 * @date      2017-07-16 08:18:12
 * @note Change Log
 * - v1.0.0-alpha.0 - Initial version        - 2017-07-16 wer
 * @todo Ritc/Library/Exceptions/HelperException.php - Everything
 */
namespace Ritc\Library\Exceptions;

use Ritc\Library\Abstracts\CustomException;

/**
 * Class HelperException.
 * @class   HelperException
 * @package Ritc\Library\Exceptions
 */
class HelperException extends CustomException
{
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getCodeText($code = -1)
    {
        switch ($code) {
            case 10:
                return 'Unable to start the helper.';
            case 20:
                return '__clone not allowed for this service.';
            default:
                return parent::getCodeText($code);
        }
    }
}