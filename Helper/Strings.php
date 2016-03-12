<?php
/**
 * @brief     Modifies strings and strings in arrays.
 * @details   Methods which start with make_ return a modified version
 *            of the value passed into the method, usually indicated by
 *            the name of the method.
 *            Renamed and modified version of old class Output.
 * @ingroup   ritc_library lib_helper
 * @file      Ritc/Library/Helper/Strings.php
 * @namespace Ritc\Library\Helper
 * @class     Strings
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   6.3.0
 * @date      2016-02-24 13:11:07
 * @note <b>Change Log</b>
 * - v6.3.0 - added new method to translate a digit to an English word          - 02/24/2016 wer
 * - v6.2.0 - added new method to strip tags from htmlentites coded string      - 11/25/2015 wer
 * - v6.1.0 - renamed makeAlphanumeric to makeAlphanumericPlus and added two    - 11/07/2015 wer
 *              new methods called makeAlphanumeric and makeCamelCase.
 * - v6.0.0 - changed all methods to static                                     - 01/27/2015 wer
 * - v5.1.2 - moved to the Helper namespace                                     - 11/15/2014 wer
 * - v5.1.1 - changed to implment the changes in Base class                     - 09/23/2014 wer
 * - v5.1.0 - added formatPhoneNumber method.                                   - 2013-05-14 wer
 * - v5.0.1 - bug fixes and removed unused code left over from old class Output - 2013-05-01 wer
 * - v5.0.0 - renamed new version for RITC Library v5
 */
namespace Ritc\Library\Helper;

class Strings
{
    /**
     * Returns the English string for the digit, e.g., 1 = 'one'
     * @param int $var
     * @return string
     */
    public static function digitToString($var = 0)
    {
        switch ($var) {
            case 0:
                return 'zero';
            case 1;
                return 'one';
            case 2;
                return 'two';
            case 3;
                return 'three';
            case 4;
                return 'four';
            case 5;
                return 'five';
            case 6;
                return 'six';
            case 7;
                return 'seven';
            case 8;
                return 'eight';
            case 9;
                return 'nine';
        }
    }

    /**
     * Changes the phone number to the specified phone format (or default format)
     * This works only for US numbers and is not international (yet).
     *
     * @param string $phone_number required defaults to empty str
     * @param string $phone_format optional format to change to
     *                            options are 'AAA-BBB-CCCC', '(AAA) BBB-CCCC', 'AAA BBB CCCC', 'AAA.BBB.CCC.DDDD'
     *                            or the generic 'XXX-XXX-XXXX', '(XXX) XXX-XXXX', 'XXX XXX XXXX', 'XXX.XXX.XXXX'
     *
     * @return mixed|string
     */
    public static function formatPhoneNumber($phone_number = '', $phone_format = 'AAA-BBB-CCCC')
    {
        if ($phone_number == '') { return ''; }
        $phone_number = preg_replace("/[^0-9]/", "", $phone_number);
        $strlen = strlen($phone_number);

        switch ( $strlen ) {
            case 7:
                return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $phone_number);
                break;
            case 10:
                switch ($phone_format) {
                    case '(XXX) XXX-XXXX':
                    case '(AAA) BBB-CCCC':
                        return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $phone_number);
                    case 'XXX XXX XXXX':
                    case 'AAA BBB CCCC':
                        return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "$1 $2 $3", $phone_number);
                    case 'XXX.XXX.XXXX':
                    case 'AAA.BBB.CCCC':
                        return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "$1.$2.$3", $phone_number);
                    case 'XXX-XXX-XXXX':
                    case 'AAA-BBB-CCCC':
                    default:
                        return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "$1-$2-$3", $phone_number);
                }
                break;
            case 11:
                switch ($phone_format) {
                    case '(XXX) XXX-XXXX':
                    case '(AAA) BBB-CCCC':
                        return preg_replace("/([0-9]{1})([0-9]{3})([0-9]{3})([0-9]{4})/", "$1 ($2) $3-$4", $phone_number);
                    case 'XXX XXX XXXX':
                    case 'AAA BBB CCCC':
                        return preg_replace("/([0-9]{1})([0-9]{3})([0-9]{3})([0-9]{4})/", "$1 $2 $3 $4", $phone_number);
                    case 'XXX.XXX.XXXX':
                    case 'AAA.BBB.CCCC':
                        return preg_replace("/([0-9]{1})([0-9]{3})([0-9]{3})([0-9]{4})/", "$1.$2.$3.$4", $phone_number);
                    case 'XXX-XXX-XXXX':
                    case 'AAA-BBB-CCCC':
                    default:
                        return preg_replace("/([0-9]{1})([0-9]{3})([0-9]{3})([0-9]{4})/", "$1 $2-$3-$4", $phone_number);
                }
            default:
                return $phone_number;
        }
    }
    /**
     * Takes the input and makes it a boolean.
     * Basically, looks for the boolean false, int 0, or string of false (case insensitive).
     * @param mixed $input the value to turn to a boolean
     * @return bool        the changed value
     */
    public static function isTrue($input = '')
    {
        if (is_bool($input)) {
            return $input;
        }
        elseif ($input === NULL || $input === 'NULL' || $input === 'null') {
            return false;
        }
        elseif (is_string($input)) {
            $input = strtoupper(trim($input));
            if (ctype_digit($input)) {
                $input = (int) $input === 0 ? 'false' : 'true';
            }
            switch ($input) {
                case 'FALSE':
                case 'false':
                case 'NO':
                case 'OFF':
                case '':
                    return false;
                    break;
                default:
                    return true;
            }
        }
        elseif (ctype_digit((string) $input)) {
            return ((int) $input === 0 ? false : true);
        }
        elseif (is_array($input)) {
            return false;
        }
        elseif (is_object($input)){
            return false;
        }
        else {
            return true; // true just for default
        }
    }
    /**
     * Turns a string into pure alpha string stripping out everything else
     * @param string $the_string
     * @return string
     */
    public static function makeAlpha($the_string = '')
    {
        return preg_replace("/[^a-zA-Z]/", '', $the_string);
    }
    /**
     * Removes everything except letters and numbers.
     * @param string $the_string
     * @return string the modified string
     */
    public static function makeAlphanumeric($the_string = '')
    {
        return preg_replace("/[^a-zA-Z0-9]/", '', $the_string);
    }
    /**
     * Removes everything except letters, numbers, -, and _.
     * Removes html and php tags first, replaces spaces with undersHelpers,
     * then finally removes all other characters
     * @param string $the_string
     * @return string
     */
    public static function makeAlphanumericPlus($the_string = '')
    {
        $the_string = self::removeTags($the_string);
        $the_string = str_replace(' ', '_', $the_string);
        return preg_replace("/[^a-zA-Z0-9_\-]/", '', $the_string);
    }
    /**
     * Makes the string alpha camelCase.
     * Splits string at spaces, dashes and underscores into alpha 'words' which
     * it will uppercase all but the first word by default.
     * @param string $the_string        defaults to blank string
     * @param bool   $leave_first_alone defaults to true
     * @return string
     */
    public static function makeCamelCase($the_string = '', $leave_first_alone = true)
    {
        $the_string = self::removeTags(trim($the_string));
        if (strpos($the_string, ' ') !== false) {
            $a_string = explode(' ', $the_string);
        }
        elseif (strpos($the_string, '_') !== false) {
            $a_string = explode('_', $the_string);
        }
        elseif (strpos($the_string, '-') !== false) {
            $a_string = explode('-', $the_string);
        }
        else {
            return self::makeAlpha($the_string);
        }
        $new_string = '';
        foreach($a_string as $key => $word) {
            $word = self::makeAlpha($word);
            if ($key != 0 || $leave_first_alone === false) {
                $word = ucfirst($word);
            }
            $new_string .= $word;
        }
        return $new_string;
    }
    /**
     * Makes the string alphanumeric plus _*.+!- in all lower case.
     * Removes html and php tags first, replaces spaces with undersHelpers,
     * removes all other characters, then finally make lowercase.
     * @param string $the_string
     * @return string the modified string
     */
    public static function makeInternetUsable($the_string = '')
    {
        $the_string = self::removeTags($the_string);
        $the_string = str_replace(' ', '_', $the_string);
        $the_string = preg_replace("/[^a-zA-Z0-9_*.+!\-]/", '', $the_string);
        return strtolower($the_string);
    }
    /**
     * Turns the string into sentence case.
     * Allows one to specify specific words to be cased as desired.
     * @param string $the_string
     * @param array  $a_special_words array ['replace_this' => '', 'with_this' => '']
     * @return string
     */
    public static function makeSentenceCase($the_string = '', array $a_special_words = array())
    {
        $return_this = '';
        $a_sentences = preg_split('/([.?!]+)/', $the_string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        foreach($a_sentences as $sentence) {
            $return_this .= $sentence == '.' ? '. ' : ucfirst(strtolower(trim($sentence)));
        }
        if ($a_special_words != array()) {
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
     * @note this method strips all html and php tags before shortening the
     *     string.
     * @param string $string       string to be shortened.
     * @param int    $num_of_words number of words, defaults to 5
     * @param int    $num_of_chars number of characters, if not '', the method
     *                             uses this param to shorten the string
     * @return string - short string.
     */
    public static function makeShortString($string = '', $num_of_words = 0, $num_of_chars = 0)
    {
        if ($string == '') {
            return '';
        }
        $string = self::removeTags($string);
        $this_string = '';
        $that_string = '';
        if ((int) $num_of_words === 0 && (int) $num_of_chars === 0) {
            $this_string = self::makeShortString($string, 5, 0);
        }
        elseif ((int) $num_of_words === 0 && (int) $num_of_chars > 0) {
            for ($i=0;$i < $num_of_chars; $i++) {
                $this_string .= $string{$i};
            }
        }
        elseif ((int) $num_of_words > 0 && (int) $num_of_chars === 0) {
            $string_parts = explode(' ', $string);
            if (count($string_parts) < $num_of_words) {
                $num_of_words = count($string_parts);
            }
            for ($i = 0; $i < $num_of_words; $i++) {
                $this_string .= $this_string == '' ? '' : ' ';
                $this_string .= $string_parts[$i];
            }
        }
        else {
            $string_parts = explode(' ', $string);
            for ($i=0; $i < $num_of_words; $i++) {
                $that_string .= $that_string == '' ? '' : ' ';
                $this_string = $that_string;
                $this_string .= $string_parts[$i];
                if (strlen($this_string) <= $num_of_chars) {
                    $that_string = $this_string;
                } else {
                    $i = $num_of_words;
                }
            }
            $this_string = $that_string;
        }
        return trim($this_string);
    }
    /**
     * Removes the image tag from the string.
     * @param string $string optional, defaults to empty
     * @return string
     */
    public static function removeImages($string = '')
    {
        $search = [
            '@<img[^>]*?>@si',
            '@<img[^>]*? />@si',
            '@<img[^>]*?/>@si'
        ];
        $replace = ['', ''];
        return preg_replace($search, $replace, $string);
    }
    /**
     * Remove HTML tags, javascript sections and white space.
     * Idea taken from php.net documentation.
     * @param string $html
     * @return string
     */
    public static function removeTags($html = '')
    {
        $search = [
            '@<script[^>]*?>.*?</script>@si', // Strip out javascript
            '@<[\/\!]*?[^<>]*?>@si',          // Strip out HTML tags
            '@([\r\n])[\s]+@'                 // evaluate as php
        ];
        $replace = ['', '', '\1'];
        return preg_replace($search, $replace, $html);
    }
    /**
     * Remove HTML tags, javascript sections and white space.
     * Decodes htmlentities first.
     * Idea taken from php.net documentation.
     * @param string $string
     * @param int    $ent_flag defaults to ENT_QUOTES
     * @return string
     */
    public static function removeTagsWithDecode($string = '', $ent_flag = ENT_QUOTES)
    {
        $search = [
            '@<script[^>]*?>.*?</script>@si', // Strip out javascript
            '@<[\/\!]*?[^<>]*?>@si',          // Strip out HTML tags
            '@([\r\n])[\s]+@'                 // evaluate as php
        ];
        $replace = ['', '', '\1'];
        $string = html_entity_decode($string, $ent_flag);
        return preg_replace($search, $replace, $string);
    }

}
