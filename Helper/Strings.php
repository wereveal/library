<?php
/**
 *  @brief Modifies strings and strings in arrays.
 *  @details Methods which start with make_ return a modified version
 *  of the value passed into the method, usually indicated by
 *  the name of the method.
 *  Renamed and modified version of old class Output.
 *  @file Strings.php
 *  @ingroup ritc_library helper
 *  @namespace Ritc/Library/Helper
 *  @class Strings
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 5.1.2
 *  @date 2014-11-15 12:30:05
 *  @note A part of the RITC Library
 *  @note <pre><b>Change Log</b>
 *      v5.1.2 - moved to the Helper namespace - 11/15/2014 wer
 *      v5.1.1 - changed to implment the changes in Base class - 09/23/2014 wer
 *      v5.1.0 - added formatPhoneNumber method. 2013-05-14
 *      v5.0.1 - bug fixes and removed unused code left over from old class Output 2013-05-01
 *      v5.0.0 - renamed new version for RITC Library v5
 *  </pre>
**/
namespace Ritc\Library\Helper;

use Ritc\Library\Abstracts\Base;

class Strings extends Base
{
    protected $the_original_string = 'Start';
    protected $the_modified_string = '';
    public function __construct()
    {
        $this->setPrivateProperties();
    }

    ### String Methods ###
    /**
     * Changes the phone number to the specified phone format (or default format)
     * This works only for US numbers and is not international (yet).
     *
     * @param string $phone_number required defaults to empty str
     * @param string $phone_format optional format to change to
     *                             options are 'AAA-BBB-CCCC', '(AAA) BBB-CCCC', 'AAA BBB CCCC', 'AAA.BBB.CCC.DDDD'
     *                             or the generic 'XXX-XXX-XXXX', '(XXX) XXX-XXXX', 'XXX XXX XXXX', 'XXX.XXX.XXXX'
     *
     * @return mixed|string
     */
    public function formatPhoneNumber($phone_number = '', $phone_format = 'AAA-BBB-CCCC')
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
     *  Takes the input and makes it a boolean.
     *  Basically, looks for the boolean false, int 0, or string of false (case insensitive).
     *  @param $input (mixed) - the value to turn to a boolean
     *  @return bool - the changed value
    **/
    public function isTrue($input = '')
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
                $input = ((int) $input === 0 ? 'false' : 'true');
            }
            switch ($input) {
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
     *  Turns a string into pure alpha string stripping out everything else
     *  @param string $the_string
     *  @return string
    **/
    public function makeAlpha($the_string = '')
    {
        return preg_replace("/[^a-zA-Z]/", '', $the_string);
    }
    /**
     *  Removes everything except letters, numbers, -, and _.
     *  Removes html and php tags first, replaces spaces with undersHelpers,
     *  then finally removes all other characters
     *  @param $the_string (str)
     *  @return string - the modified string
    **/
    public function makeAlphanumeric($the_string = '')
    {
        if ($the_string == '') {
            $the_string = $this->the_original_string;
        } else {
            $this->the_original_string = $the_string;
        }
        $the_string = $this->removeTags($the_string);
        $the_string = str_replace(' ', '_', $the_string);
        $new_string = preg_replace("/[^a-zA-Z0-9_\-]/", '', $the_string);
        $this->the_modified_string = $new_string;
        return $new_string;
    }
    /**
     *  Makes the string alphanumeric plus _*.+!- in all lower case.
     *  Removes html and php tags first, replaces spaces with undersHelpers,
     *  removes all other characters, then finally make lowercase
     *  @param $the_string (str)
     *  @return string - the modified string
    **/
    public function makeInternetUsable($the_string = '')
    {
        if ($the_string == '') {
            $the_string = $this->the_original_string;
        } else {
            $this->the_original_string = $the_string;
        }
        $the_string = $this->removeTags($the_string);
        $the_string = str_replace(' ', '_', $the_string);
        $the_string = preg_replace("/[^a-zA-Z0-9_*.+!\-]/", '', $the_string);
        $new_string = strtolower($the_string);
        $this->the_modified_string = $new_string;
        return $new_string;
    }
    public function makeSentenceCase($the_string = '', $a_capped_words = array())
    {
        if ($the_string == '') {
            $the_string = $this->the_original_string;
        } else {
            $this->the_original_string = $the_string;
        }
        $return_this = '';
        $a_sentences = preg_split('/([.?!]+)/', $the_string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $this->logIt("PREG_SPLIT: " . var_export($a_sentences, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
        foreach($a_sentences as $sentence) {
            $return_this .= $sentence == '.' ? '. ' : ucfirst(strtolower(trim($sentence)));
        }
        if ($a_capped_words != array()) {
            $return_this = str_replace($a_capped_words['replace_this'], $a_capped_words['with_this'], $return_this);
        }
        $return_this = trim($return_this);
        $this->the_modified_string = $return_this;
        return $return_this;
    }
    /**
     *  Shortens a string.
     *  Can shorten a string based on the number of words or the number
     *      of characters. It uses two main parameters, number of words and
     *      the number of characters. If only number of words is specified, it
     *      will return exactly that many words. If only the number of chars
     *      is specified, it will return exactly that many characters. If both
     *      number of words and number of chars are greater than 0, it will
     *      return full words, up to the number of words but no more than the
     *      number of characters, i.e., it could return less words than the
     *      number of words if the number of characters restricts it.
     *  @note this method strips all html and php tags before shortening the
     *      string.
     *  @param $string (str) - string to be shortened.
     *  @param $num_of_words (int) - number of words, defaults to 5
     *  @param $num_of_chars (int) - number of characters, if not '', the
     *      method uses this param to shorten the string
     *  @return string - short string.
    **/
    public function makeShortString($string = '', $num_of_words = 0, $num_of_chars = 0)
    {
        if ($string == '') {
            return '';
        }
        $this->the_original_string = $string;
        $string = $this->removeTags($string);
        $this_string = '';
        $that_string = '';
        $this->logIt("The string before shortening: $string", LOG_OFF, __METHOD__ . '.' . __LINE__);
        if ((int) $num_of_words === 0 && (int) $num_of_chars === 0) {
            $this_string = $this->makeShortString($string, 5, 0);
        } elseif ((int) $num_of_words === 0 && (int) $num_of_chars > 0) {
            $this->logIt("In num of chars", LOG_OFF, __METHOD__ . '.' . __LINE__);
            for ($i=0;$i < $num_of_chars; $i++) {
                $this_string .= $string{$i};
            }
        } elseif ((int) $num_of_words > 0 && (int) $num_of_chars === 0) {
            $string_parts = explode(' ', $string);
            if (count($string_parts) < $num_of_words) {
                $num_of_words = count($string_parts);
            }
            for ($i = 0; $i < $num_of_words; $i++) {
                $this_string .= $this_string == '' ? '' : ' ';
                $this_string .= $string_parts[$i];
            }
        } else {
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
        $this_string = trim($this_string);
        $this->the_modified_string = $this_string;
        return $this_string;
    }
    /**
     *  Removes the image tag from the string.
     *  @param string $string optional, defaults to empty
     *  @return string
    **/
    public function removeImages($string = '')
    {
        if ($string == '') {
            $string = $this->the_original_string;
        } else {
            $this->the_original_string = $string;
        }
        $search = array('@<img[^>]*?>@si', '@<img[^>]*? />@si', '@<img[^>]*?/>@si');
        $replace = array('', '');
        $text = preg_replace($search, $replace, $string);
        $this->the_modified_string = $text;
        return $text;
    }
    /**
     *  Remove HTML tags, javascript sections and white space.
     *  idea taken from php.net documentation
     *  @param string $html
     *  @return string
    **/
    public function removeTags($html = '')
    {
        $search = array ('@<script[^>]*?>.*?</script>@si', // Strip out javascript
                          '@<[\/\!]*?[^<>]*?>@si',          // Strip out HTML tags
                          '@([\r\n])[\s]+@');              // evaluate as php
        $replace = array ('', '', '\1');
        $text = preg_replace($search, $replace, $html);
        return $text;
    }

    ### Getters and Setters ###
    public function getVar($var_name = '')
    {
        if (isset($this->$var_name)) {
            return $this->$var_name;
        }
        return false;
    }
    public function getTheModifiedString()
    {
        return $this->the_modified_string;
    }
    public function getTheOriginalString()
    {
        return $this->the_original_string;
    }
    public function setTheOriginalString($the_string = '')
    {
        $this->the_original_string = $the_string;
    }

}
