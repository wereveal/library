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
 * @version v1.0.0-alpha.1
 * @date    2021-11-26 16:38:17
 * @change_log
 * - v1.0.0-alpha.1 - updated for php8                          - 2021-11-26 wer
 * - v1.0.0-alpha.0 - Initial version                           - 2017-07-16 wer
 */
class HelperException extends CustomExceptionAbstract
{
    /**
     * @param string $value
     * @return int
     */
    public function getCodeNumber(string $value = ''):int
    {
        return match($value) {
            'missing_values', 'invalid_values'  => 100,
            default                             => parent::getCodeNumber($value)
        };
    }

    /**
     * Returns the text string associated with the error code.
     *
     * @param int $code
     * @return string
     */
    public function getCodeText(int $code = -1):string
    {
        return match ($code) {
            10      => 'Unable to start the helper.',
            20      => '__clone not allowed for this service.',
            100     => 'Missing or invalid values.',
            default => parent::getCodeText($code),
        };
    }
}
