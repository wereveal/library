<?php
/**
 * Class ServiceException
 * @package Ritc_Library
 */
namespace Ritc\Library\Exceptions;

use Ritc\Library\Abstracts\CustomException;

/**
 * Class ServiceException - Custom Exceptions for Services.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-alpha.0
 * @date    2017-07-16 08:18:12
 * ## Change Log
 * - v1.0.0-alpha.0 - Initial version        - 2017-07-16 wer
 */
class ServiceException extends CustomException
{
    /**
     * ServiceException constructor.
     * @param string          $message
     * @param int             $code
     * @param \Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Returns the text string for the error code.
     * @param int $code
     * @return string
     */
    public function getCodeText($code = -1)
    {
        switch ($code) {
            case 10:
                return 'Unable to start the service.';
            case 20:
                return '__clone not allowed for this service.';
            default:
                return parent::getCodeText($code);
        }
    }
}
