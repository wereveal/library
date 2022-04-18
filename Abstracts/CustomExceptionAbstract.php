<?php
/**
 * Class CustomExceptionAbstract
 * @package Ritc_Library
 */
namespace Ritc\Library\Abstracts;

use Exception;
use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Interfaces\CustomExceptionInterface;

/**
 * Class CustomExceptionAbstract Abstract which extends php Exception class.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 2.0.0
 * @date    2021-11-26 13:53:45
 * @change_log
 * - v2.0.0 - compatibility to php 8                            - 2021-11-26 wer
 * - v1.0.0 - changed to production, added phpDoc               - 2018-03-07 wer
 */
abstract class CustomExceptionAbstract extends Exception implements CustomExceptionInterface
{
    /**
     * Overrides \Exception
     *
     * @return string
     */
    public function __toString()
    {
        return get_class($this) .
        " '{$this->message}' in {$this->file}.{$this->line}\n" .
        $this->getTraceAsString();
    }

    /**
     * Required by Interface.
     *
     * @return string
     */
    public function errorMessage():string
    {
        $error_message = $this->getMessage();
        if (empty($error_message)) {
            $error_message = $this->getCodeText($this->getCode());
        }
        $error_message .= "\n -- " . $this->getClass() . '.' . $this->getLine();

        $previous = $this->getPrevious();
        if ($previous) {
            $msg  = $previous->getMessage();
            $code = $previous->getCode();
            $line = $previous->getLine();
            $file = $previous->getFile();
            $error_message .= "\n- Previous: {$msg}\n -- $file.$line (code: $code)";
        }
        return $error_message;
    }

    public function getCodeNumber(string $value = ''):int
    {
        return ExceptionHelper::getCodeNumber($value);
    }

    /**
     * Required by Interface.
     *
     * @param int $code
     * @return string
     */
    public function getCodeText(int $code = -1):string
    {
        return ExceptionHelper::getCodeText($code);
    }

    /**
     * Gets the name of the custom exception.
     *
     * @return string
     */
    public function getClass():string
    {
        return get_class($this);
    }
}
