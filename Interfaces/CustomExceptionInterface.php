<?php
namespace Ritc\Library\Interfaces;

/**
 * Interface CustomExceptionInterface
 *
 * @package RITC_Library
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0
 * @date    2018-03-07 08:50:58
 * ## Change Log
 * - v1.0.0         Production, no changes had taken place since initial                                - 2018-03-07 wer
 * - v1.0.0.alpha-0 initial version                                                                     - 2016-10-17 wer
 */
interface CustomExceptionInterface
{
    /* Protected methods inherited from Exception class */
    public function getMessage();                 // Exception message
    public function getCode();                    // User-defined Exception code
    public function getFile();                    // Source filename
    public function getLine();                    // Source line
    public function getTrace();                   // An array of the backtrace()
    public function getTraceAsString();           // Formated string of trace

    /* Overrides methods inherited from Exception class */
    public function __toString();                 // formated string for display

    /* Adds methods to the Exception class */
    public function errorMessage();               // error message with additional information
    public function getCodeText();                // associate text with a code
}
