<?php
/**
 * @brief     Custom Exceptions for Factorys.
 * @details   Handles custom exceptions for services.
 * @ingroup   lib_exceptions
 * @file      Ritc/Library/Exceptions/FactoryException.php
 * @namespace Ritc\Library\Exceptions
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.0
 * @date      2017-07-16 08:18:12
 * @note Change Log
 * - v1.0.0-alpha.0 - Initial version        - 2017-07-16 wer
 * @todo Ritc/Library/Exceptions/FactoryException.php - Everything
 */
namespace Ritc\Library\Exceptions;

use Ritc\Library\Abstracts\CustomException;
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
        switch ($code) {
            case 10:
                return 'Unable to start the factory.';
            case 20:
                return '__clone not allowed for this factory.';
            case 30:
                return 'Invalid file type for configuration.';
            case 40:
                return 'Unable to get configuration for factory.';
            case 100:
                return 'Factory unable to create the object instance.';
            case 110:
                return 'Factory unable to create an object needed to create the object instance.';
            default:
                return parent::getCodeText($code);
        }
    }
}