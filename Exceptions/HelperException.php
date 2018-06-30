<?php
/**
 * Class HelperException
 * @package Ritc_Library
 */
namespace Ritc\Library\Exceptions;

use Ritc\Library\Abstracts\CustomExceptionAbstract;

/**
 * Class HelperException - Handles custom exceptions for helpers.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-alpha.0
 * @date    2017-07-16 08:18:12
 * @change_log
 * - v1.0.0-alpha.0 - Initial version        - 2017-07-16 wer
 */
class HelperException extends CustomExceptionAbstract
{
    /**
     * Returns the text string associated with the error code.
     *
     * @param int $code
     * @return string
     */
    public function getCodeText($code = -1):string
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
