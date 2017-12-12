<?php
/**
 * @brief     Custom Exceptions for Factorys.
 * @details   Handles custom exceptions for services.
 * @ingroup   lib_exceptions
 * @file      Ritc/Library/Exceptions/FactoryException.php
 * @namespace Ritc\Library\Exceptions
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.1
 * @date      2017-12-12 14:57:04
 * @note Change Log
 * - v1.0.0-alpha.1 - implements ExceptionHelper            - 2017-12-12 wer
 * - v1.0.0-alpha.0 - Initial version                       - 2017-07-16 wer
 * @todo Ritc/Library/Exceptions/FactoryException.php - Everything
 */
namespace Ritc\Library\Exceptions;

use Ritc\Library\Abstracts\CustomException;
use Ritc\Library\Helper\ExceptionHelper;
use Throwable;

/**
 * Class FactoryException.
 * @class   FactoryException
 * @package Ritc\Library\Exceptions
 */
class FactoryException extends CustomException
{
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getCodeText($code = -1)
    {
        return ExceptionHelper::getCodeTextFactory($code);
    }
}
