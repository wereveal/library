<?php /** @noinspection RegExpRedundantEscape */
/**
 * @noinspection PhpUndefinedConstantInspection
 * @noinspection NotOptimalRegularExpressionsInspection
 */

/**
 * Class Strings
 * @package Ritc_Library
 */
namespace Ritc\Library\Helper;

/**
 * Modifies strings.
 * Methods which start with make return a modified version
 * of the value passed into the method, usually indicated by
 * the name of the method.
 * Renamed and modified version of old class Output which was the class name before v5.0.0.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 7.0.0
 * @date    2021-11-26 16:06:45
 * @change_log
 * - v7.0.0  - updated for php8                                                 - 2021-11-26 wer
 * - v6.9.0  - added new method to strip characters from the end of a string    - 2018-11-06 wer
 * - v6.8.0  - added new methods makeGoodUrl, makeValidUrlScheme                - 2018-06-13 wer
 * - v6.7.0  - changed makeInternetUsable, backwards compatible                 - 2018-06-08 wer
 * - v6.6.0  - added new method to convert url to cache compatible string       - 2018-05-15 wer
 * - v6.5.0  - add new method to convert column number to Excel column letters  - 2018-03-06 wer
 * - v6.4.0  - added new method to trim slashes from front and back of string   - 2016-09-08 wer
 * - v6.3.0  - added new method to translate a digit to an English word         - 02/24/2016 wer
 * - v6.2.0  - added new method to strip tags from htmlentites coded string     - 11/25/2015 wer
 * - v6.1.0  - renamed makeAlphanumeric to makeAlphanumericPlus and added two   - 11/07/2015 wer
 *             new methods called makeAlphanumeric and makeCamelCase.
 * - v6.0.0  - changed all methods to static                                    - 01/27/2015 wer
 * - v5.1.2  - moved to the Helper namespace                                    - 11/15/2014 wer
 * - v5.1.1  - changed to implment the changes in Base class                    - 09/23/2014 wer
 * - v5.1.0  - added formatPhoneNumber method.                                  - 2013-05-14 wer
 * - v5.0.0  - renamed new version for RITC Library v5
 */
class Strings
{
    /**
     * Returns the English string for the digit, e.g., 1 = 'one'
     *
     * @param int $var
     * @return string
     */
    public static function digitToString(int $var = 0):string
    {
        return match ($var) {
            0       => 'zero',
            1       => 'one',
            2       => 'two',
            3       => 'three',
            4       => 'four',
            5       => 'five',
            6       => 'six',
            7       => 'seven',
            8       => 'eight',
            9       => 'nine',
            default => '',
        };
    }

    /**
     * Changes the phone number to the specified phone format (or default format)
     * This works only for US numbers and is not international (yet).
     *
     * @param string $phone_number required defaults to empty str
     * @param string $phone_format optional format to change to
     *                             options are 'AAA-BBB-CCCC', '(AAA) BBB-CCCC', 'AAA BBB CCCC', 'AAA.BBB.CCC.DDDD'
     *                             or the generic 'XXX-XXX-XXXX', '(XXX) XXX-XXXX', 'XXX XXX XXXX', 'XXX.XXX.XXXX'
     *
     * @return string|array|null
     */
    public static function formatPhoneNumber(string $phone_number = '', string $phone_format = 'AAA-BBB-CCCC'): string|array|null
    {
        if ($phone_number === '') { return ''; }
        $phone_number = preg_replace('/\D/', '', $phone_number);
        $strlen = strlen($phone_number);
        return match ($strlen) {
            7       => preg_replace('/(\d{3})(\d{4})/', '$1-$2', $phone_number),
            10      => match ($phone_format) {
                '(XXX) XXX-XXXX', '(AAA) BBB-CCCC' => preg_replace(
                    '/(\d{3})(\d{3})(\d{4})/',
                    '($1) $2-$3',
                    $phone_number
                ),
                'XXX XXX XXXX', 'AAA BBB CCCC'     => preg_replace(
                    '/(\d{3})(\d{3})(\d{4})/',
                    '$1 $2 $3',
                    $phone_number
                ),
                'XXX.XXX.XXXX', 'AAA.BBB.CCCC'     => preg_replace(
                    '/(\d{3})(\d{3})(\d{4})/',
                    '$1.$2.$3',
                    $phone_number
                ),
                default                            => preg_replace(
                    '/(\d{3})(\d{3})(\d{4})/',
                    '$1-$2-$3',
                    $phone_number
                ),
            },
            11      => match ($phone_format) {
                '(XXX) XXX-XXXX', '(AAA) BBB-CCCC' => preg_replace(
                    '/(\d)(\d{3})(\d{3})(\d{4})/',
                    '$1 ($2) $3-$4',
                    $phone_number
                ),
                'XXX XXX XXXX', 'AAA BBB CCCC'     => preg_replace(
                    '/(\d)(\d{3})(\d{3})(\d{4})/',
                    '$1 $2 $3 $4',
                    $phone_number
                ),
                'XXX.XXX.XXXX', 'AAA.BBB.CCCC'     => preg_replace(
                    '/(\d)(\d{3})(\d{3})(\d{4})/',
                    '$1.$2.$3.$4',
                    $phone_number
                ),
                default                            => preg_replace(
                    '/(\d)(\d{3})(\d{3})(\d{4})/',
                    '$1 $2-$3-$4',
                    $phone_number
                ),
            },
            default => $phone_number,
        };
    }

    /**
     * Takes the input and makes it a boolean.
     * Basically, looks for the boolean false, int 0, or string of false (case insensitive).
     *
     * @param mixed $input the value to turn to a boolean
     * @return bool        the changed value
     */
    public static function isTrue(mixed $input):bool
    {
        if (is_bool($input)) {
            return $input;
        }
        if ($input === NULL || $input === 'NULL' || $input === 'null') {
            return false;
        }
        if (is_string($input)) {
            $input = strtoupper(trim($input));
            if (ctype_digit($input)) {
                $input = (int) $input === 0 ? 'false' : 'true';
            }
            return match ($input) {
                'FALSE', 'false', 'NO', 'OFF', '' => false,
                default                           => true,
            };
        }
        if (ctype_digit($input)) {
            return ((int)$input !== 0);
        }
        if (is_array($input)) {
            return false;
        }
        if (is_object($input)) {
            return false;
        }
        return true; // true just for default
    }

    /**
     * Turns a string into pure alpha string stripping out everything else.
     *
     * @param string $the_string
     * @return string
     */
    public static function makeAlpha(string $the_string = ''):string
    {
        return preg_replace('/[^a-zA-Z]/', '', $the_string);
    }

    /**
     * Removes everything except letters and numbers.
     *
     * @param string $the_string
     * @return string the modified string
     */
    public static function makeAlphanumeric(string $the_string = ''):string
    {
        return preg_replace('/[^a-zA-Z\d]/', '', $the_string);
    }

    /**
     * Removes everything except letters, numbers, -, and _.
     * Removes html and php tags first, replaces spaces with underline,
     * then finally removes all other characters
     *
     * @param string $the_string
     * @return string
     */
    public static function makeAlphanumericPlus(string $the_string = ''):string
    {
        $the_string = self::removeTags($the_string);
        $the_string = str_replace(' ', '_', $the_string);
        return preg_replace('/[^a-zA-Z\d_\-]/', '', $the_string);
    }

    /**
     * Makes the string alpha camelCase or CamelCase depending on 2nd param.
     * Splits string at spaces, dashes and underscores into alpha 'words' which
     * it will uppercase all but the first word by default.
     *
     * @param string $the_string        defaults to blank string
     * @param bool   $leave_first_alone defaults to true
     * @return string
     */
    public static function makeCamelCase(string $the_string = '', bool $leave_first_alone = true):string
    {
        $the_string = self::removeTags(trim($the_string));
        if (str_contains($the_string, ' ')) {
            $a_string = explode(' ', $the_string);
        }
        elseif (str_contains($the_string, '_')) {
            $a_string = explode('_', $the_string);
        }
        elseif (str_contains($the_string, '-')) {
            $a_string = explode('-', $the_string);
        }
        else {
            if ($leave_first_alone === false) {
                $the_string = ucfirst($the_string);
            }
            return self::makeAlpha($the_string);
        }
        $new_string = '';
        foreach($a_string as $key => $word) {
            $word = self::makeAlpha($word);
            if ($key > 0 || $leave_first_alone === false) {
                $word = ucfirst($word);
            }
            $new_string .= $word;
        }
        return $new_string;
    }

    /**
     * Does a few more things than raw filter_var($the_string, FILTER_SANITIZE_URL).
     * Replaces spaces with underscores and removes bad stuff before running string
     * through filter_var($the_string, FILTER_SANITIZE_URL).
     *
     * @param string $the_string
     * @param bool   $allow_upper
     * @return string
     */
    public static function makeGoodUrl(string $the_string = '', bool $allow_upper = true):string
    {
        $the_string = self::removeTags($the_string);
        $the_string = str_replace(' ', '_', $the_string);
        $the_string = filter_var($the_string, FILTER_SANITIZE_URL);
        if ($allow_upper === false) {
            $the_string = strtolower($the_string);
        }
        $test_string = $the_string;
        if (empty(strpos($test_string, '://'))) {
            $test_string = SITE_URL . $test_string;
        }
        else {
            [$scheme, $the_rest] = explode('://', $test_string);
            $scheme = self::makeValidUrlScheme($scheme);
            $test_string = $scheme . '://' . $the_rest;
            $the_string = $test_string;
        }
        if (filter_var($test_string, FILTER_VALIDATE_URL) === false) {
            $the_string = '';
        }
        return $the_string;
    }

    /**
     * Makes the string alphanumeric plus _*.+!-~ in all lower case.
     * This is an extension of filter_var($the_string, FILTER_SANITIZE_URL) using
     * a subset of allowed characters and lowercase only. Will not work with URLs.
     *
     * @param string $the_string
     * @return string the modified string
     */
    public static function makeInternetUsable(string $the_string = ''):string
    {
        $the_string = self::removeTags($the_string);
        $the_string = str_replace(' ', '_', $the_string);
        $the_string = filter_var($the_string, FILTER_SANITIZE_URL);
        $the_string = preg_replace('/[^a-zA-Z\d_.+!\-~]/', '', $the_string);
        return strtolower($the_string);
    }

    /**
     * Turns the string into sentence case.
     * Allows one to specify specific words to be cased as desired.
     *
     * @param string $the_string
     * @param array  $a_special_words array ['replace_this' => '', 'with_this' => '']
     * @return string
     */
    public static function makeSentenceCase(string $the_string = '', array $a_special_words = array()):string
    {
        $return_this = '';
        $a_sentences = preg_split('/([.?!]+)/', $the_string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        foreach($a_sentences as $sentence) {
            $return_this .= $sentence === '.' ? '. ' : ucfirst(strtolower(trim($sentence)));
        }
        if ($a_special_words !== []) {
            $return_this = str_replace($a_special_words['replace_this'], $a_special_words['with_this'], $return_this);
        }
        return trim($return_this);
    }

    /**
     * Shortens a string.
     * Can shorten a string based on the number of words or the number
     *     of characters. It uses two main parameters, number of words and
     *     the number of characters. If only number of words is specified, it
     *     will return exactly that many words. If only the number of chars
     *     is specified, it will return exactly that many characters. If both
     *     number of words and number of chars are greater than 0, it will
     *     return full words, up to the number of words but no more than the
     *     number of characters, i.e., it could return less words than the
     *     number of words if the number of characters restricts it.
     *
     * @note this method strips all html and php tags before shortening the
     *     string.
     *
     * @param string $string       string to be shortened.
     * @param int    $num_of_words number of words, defaults to 5
     * @param int    $num_of_chars number of characters, if not '', the method
     *                             uses this param to shorten the string
     * @return string - short string.
     */
    public static function makeShortString(string $string = '', int $num_of_words = 0, int $num_of_chars = 0):string
    {
        if ($string === '') {
            return '';
        }
        $string = self::removeTags($string);
        $that_string = '';

        if ($num_of_words > 0) {
            $string_parts = explode(' ', $string);
            if ($num_of_chars < 1 && count($string_parts) <= $num_of_words) {
                return $string;
            }
            for ($i = 0; $i < $num_of_words; $i++) {
                $that_string .= $that_string ?? ' ';
                if ($num_of_chars > 0 && strlen($that_string . $string_parts[$i]) > $num_of_chars) {
                    break;
                }
                $that_string .= $string_parts[$i];
            }
            $this_string = $that_string;
        }
        elseif ($num_of_chars > 0) {
            $this_string = substr($string, 0, $num_of_chars - 1);
        }
        else {
            $this_string = self::makeShortString($string, 5);
        }
        return trim($this_string);
    }

    /**
     * Turns string into snake_case.
     * Alpha only except underscore which replaces spaces, all lowercase.
     *
     * @param string $string
     * @return string
     */
    public static function makeSnakeCase(string $string = ''): string
    {
        $string = self::removeTags($string);
        $string = str_replace(' ', '_', strtolower(trim($string)));
        return preg_replace('/[^a-z_]/', '', $string);
    }

    /**
     * Really this returns valid schemes as is and returns everything else as https.
     *
     * @param string $scheme
     * @return string
     */
    public static function makeValidUrlScheme(string $scheme = ''):string
    {
        return match ($scheme) {
            'http', 'https', 'ftp', 'gopher', 'mailto', 'file' => $scheme,
            default                                            => 'https',
        };
    }

    /**
     * Changes column numbers to column letters compatible with excel.
     *
     * @param int  $var
     * @param bool $start_w_zero
     * @return string
     */
    public static function numberToExcelColumn(int $var = 0, bool $start_w_zero = true):string
    {
        if ($start_w_zero) {
            $var++;
        }
        if ($var > 26) {
            $letter = chr((int)($var/26) + 64) . chr(($var % 26) + 64);
        }
        else {
            $letter = chr($var + 64);
        }
        return $letter;
    }

    /**
     * Removes the image tag from the string.
     *
     * @param string $string optional, defaults to empty
     * @return string
     */
    public static function removeImages(string $string = ''):string
    {
        $search = [
            '@<img[^>]*?>@i',
            '@<img[^>]*? />@i',
            '@<img[^>]*?/>@i'
        ];
        $replace = ['', ''];
        return preg_replace($search, $replace, $string);
    }

    /**
     * Removes the last character(s) of the string as specified.
     *
     * @param string $string
     * @param string $remove_this
     * @return bool|string
     */
    public static function removeLastCharacters(string $string = '', string $remove_this = ''): bool|string
    {
        return substr($string, 0, strrpos($string, $remove_this));
    }

    /**
     * Remove HTML tags, javascript sections and white space.
     * Idea taken from php.net documentation.
     *
     * @param string $html
     * @return string
     */
    public static function removeTags(string $html = ''):string
    {
        $search = [
            '@<script[^>]*?>.*?</script>@si', // Strip out javascript
            '@<[\/\!]*?[^<>]*?>@i',          // Strip out HTML tags
            '@([\r\n])\s+@'                 // evaluate as php
        ];
        $replace = ['', '', '\1'];
        return preg_replace($search, $replace, $html);
    }

    /**
     * Remove HTML tags, javascript sections and white space.
     * Decodes htmlentities first.
     * Idea taken from php.net documentation.
     *
     * @param string $string
     * @param int    $ent_flag defaults to ENT_QUOTES
     * @return string
     */
    public static function removeTagsWithDecode(string $string = '', int $ent_flag = ENT_QUOTES):string
    {
        $search = [
            '@<script[^>]*?>.*?</script>@si', // Strip out javascript
            '@<[\/\!]*?[^<>]*?>@i',          // Strip out HTML tags
            '@([\r\n])\s+@'                 // evaluate as php
        ];
        $replace = ['', '', '\1'];
        $string = html_entity_decode($string, $ent_flag);
        return preg_replace($search, $replace, $string);
    }

    /**
     * Removes starting and ending slashes.
     *
     * @param string $value
     * @return string
     */
    public static function trimSlashes(string $value):string
    {
        if (str_starts_with($value, '/')) {
            $value = substr($value, 1);
        }

        if (strrpos($value, '/') === strlen($value) - 1) {
            $value = substr($value, 0, -1);
        }

        if (str_starts_with($value, '/') || strrpos($value, '/') === strlen($value) - 1) {
            $value = self::trimSlashes($value);
        }
        return $value;
    }

    /**
     * Changes a uri to a string compatible to setting a cache key.
     *
     * @param string $value
     * @return string
     */
    public static function uriToCache(string $value = ''):string
    {
        $value = self::removeTags($value);
        $value = str_replace('/', ' ', $value);
        $value = trim($value);
        return self::makeAlphanumericPlus($value);
    }
}
