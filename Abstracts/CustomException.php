<?php
/**
 * Class CustomException
 * @package Ritc_Library
 */
namespace Ritc\Library\Abstracts;

use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Interfaces\CustomExceptionInterface;

/**
 * Class CustomException Abstract which extends php Exception class.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0
 * @date    2018-03-07 08:48:27
 * @change_log
 * - v1.0.0         - changed to production, added phpDoc                                               - 2018-03-07 wer
 */
abstract class CustomException extends \Exception implements CustomExceptionInterface
{
    /**
     * Overrides \Exception
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
     * @return string
     */
    public function errorMessage()
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

    /**
     * Required by Interface.
     * @param int $code
     * @return string
     */
    public function getCodeText($code = -1)
    {
        return ExceptionHelper::getCodeText($code);
    }

    /**
     * Gets the name of the custom exception.
     * @return string
     */
    public function getClass()
    {
        return get_class($this);
    }
}
