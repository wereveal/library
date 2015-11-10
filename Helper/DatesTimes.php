<?php
/**
 *  @brief Adds some standard use formatting methods for date and time.
 *  @file DatesTimes.php
 *  @ingroup ritc_library helper
 *  @namespace Ritc/Library/Helper
 *  @class DatesTimes
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 3.0.1
 *  @date 2014-11-15 12:33:33
 *  @note A part of the RITC Library
 *  @note <pre><b>Change Log</b>
 *      v3.0.1 - moved to Ritc\Library\Helper namespace - 11/15/2014 wer
 *      v3.0.0 - FIG standards (mostly)
 *  </pre>
**/
namespace Ritc\Library\Helper;

class DatesTimes
{
    public static function changeTimestampToMidnight($timestamp = '')
    {
        if ($timestamp == "") {
            $timestamp = time();
        }
        if (DatesTimes::isUnixTimestamp($timestamp) === false) {
            $timestamp = strtotime($timestamp);
        }
        $month = date("m", (int) $timestamp);
        $day   = date("d", (int) $timestamp);
        $year  = date("Y", (int) $timestamp);
        return mktime(0, 0, 0, $month, $day, $year);
    }
    public static function diffInDays($start_date = '', $end_date = '')
    {
        if ($interval = DatesTimes::getInterval($start_date, $end_date)) {
            return $interval->format("%R%a");
        }
        else {
            return false;
        }
    }
    public static function diffInMonths($start_date = '', $end_date = '')
    {
        if ($interval = DatesTimes::getInterval($start_date, $end_date)) {
            return $interval->format("%r%m");
        }
        else {
            return false;
        }
    }
    public static function diffInYears($start_date = '', $end_date = '')
    {
        if ($interval = DatesTimes::getInterval($start_date, $end_date)) {
            return $interval->format("%R%y");
        }
        else {
            return false;
        }
    }
    public static function diffInHours($start_time = '', $end_time = '')
    {
        if ($interval = DatesTimes::getInterval($start_time, $end_time)) {
            return $interval->format("%R%h");
        }
        else {
            return false;
        }
    }
    public static function diffInMinutes($start_time = '', $end_time = '')
    {
        if ($interval = DatesTimes::getInterval($start_time, $end_time)) {
            return $interval->format("%R%i");
        }
        else {
            return false;
        }
    }
    public static function diffInSeconds($start_time = '', $end_time = '')
    {
        if ($interval = DatesTimes::getInterval($start_time, $end_time)) {
            return $interval->format("%R%s");
        }
        else {
            return false;
        }
    }
    public static function change24hTo12h($time_string = '', $include_seconds = true, $include_meridiem = true)
    {
        if ($include_seconds) {
            $time_format = $include_meridiem ? 'g:i:s a' : 'g:i:s' ;
        }
        else {
            $time_format = $include_meridiem ? 'g:i a' : 'g:i' ;
        }
        if (DatesTimes::isUnixTimestamp($time_string)) {
            $time_string = date('m/d/Y H:i:s e', (int) $time_string);
        }
        $time_string = $time_string == '' ? date("m/d/Y H:i:s e") : $time_string ;
        try {
            $o_time = new \DateTime($time_string, new \DateTimeZone(date('e')));
            if ($o_time !== false) {
                return $o_time->format($time_format);
            }
            else {
                return null;
            }
        }
        catch (\Exception $e) {
            error_log('Caught exception: ' . $e->getMessage() . " from: " . __METHOD__ . '.' . __LINE__);
            return null;
        }
    }
    public static function convertDateWith($date_format = '', $timestamp = '', $timezone = '')
    {
        $date_format = $date_format != '' ? $date_format : 'm/d/Y' ;
        return DatesTimes::convertDateTimeWith($date_format, $timestamp, $timezone);
    }
    public static function convertDateTimeWith($date_format = '', $timestamp = '', $timezone = '')
    {
        $date_format = $date_format == '' ? \DateTime::ATOM : $date_format ;
        $date_format = DatesTimes::isValidDateFormat($date_format) ? $date_format : \DateTime::ATOM;
        if ($timestamp == '') {
            $date = date($date_format);
        }
        elseif (DatesTimes::isUnixTimestamp($timestamp)) {
            $date = date($date_format, (int) $timestamp);
        }
        else {
            $date = $timestamp;
        }
        $timezone = $timezone == '' ? date('e') : $timezone;
        try {
            $o_time = new \DateTime($date);
        }
        catch (\Exception $e) {
            error_log('Caught Exception: ' . $e->getMessage() . " from: " . __METHOD__ . '.' . __LINE__);
            return null;
        }
        $o_time->setTimeZone(new \DateTimeZone($timezone));
        return $o_time->format($date_format);
    }
    public static function getDayName($timestamp = '', $format = 'short')
    {
        $timestamp = DatesTimes::convertToUnixTimestamp($timestamp);
        return ($format == 'short' ? date('D', (int) $timestamp) : date('l', (int) $timestamp));
    }
    public static function getDayNumber($timestamp = '', $format = 'default')
    {
        $timestamp = DatesTimes::convertToUnixTimestamp($timestamp);
        switch($format) {
            case 'j':
            case 'short':
                return date('j', (int) $timestamp);
            case 'z':
            case 'doy':
                return date('z', (int) $timestamp);
            case 'd':
            case 'default':
            default:
                return date('d', (int) $timestamp);
        }
    }
    public static function getInterval($start_date = '', $end_date = '')
    {
        $start_date = $start_date == '' ? date("m/d/Y H:i:s") : $start_date;
        $end_date   = $end_date   == '' ? date("m/d/Y H:i:s") : $end_date;
        if (DatesTimes::isUnixTimestamp($start_date)) {
            $start_date = date('m/d/Y H:i:s', (int) $start_date);
        } else {
            $start_date = date('m/d/Y H:i:s', strtotime($start_date));
        }
        if (DatesTimes::isUnixTimestamp($end_date)) {
            $end_date = date('m/d/Y H:i:s', (int) $end_date);
        } else {
            $end_date = date('m/d/Y H:i:s', strtotime($end_date));
        }
        try {
            $o_start = new \DateTime($start_date);
            $o_end   = new \DateTime($end_date);
            return $o_start->diff($o_end);
        }
        catch (\Exception $e) {
            error_log('Caught exception: ',  $e->getMessage());
            return false;
        }
    }
    public static function convertToLongDateTime($timestamp = '')
    {
        $timestamp = DatesTimes::convertToUnixTimestamp($timestamp);
        return date("l, F dS, Y g:i a", (int) $timestamp);
    }
    public static function convertToLongDate($timestamp = '')
    {
        $timestamp = DatesTimes::convertToUnixTimestamp($timestamp);
        return date("l, F dS, Y", (int) $timestamp);
    }
    public static function getMeridiem($timestamp = '', $upper_case = false)
    {
        $time_format = $upper_case ? 'A' : 'a' ;
        if (DatesTimes::isUnixTimestamp($timestamp)) {
            return date($time_format, (int) $timestamp);
        } else {
            $o_date = new \DateTime($timestamp);
            return $o_date->format($time_format);
        }
    }
    public static function convertToMonth($timestamp = '', $format = 'default')
    {
        $timestamp = DatesTimes::convertToUnixTimestamp($timestamp);
        switch ($format) {
            case 'F':
            case 'full':
            case 'long':
                $format = 'F';
                break;
            case 'M':
            case 'short':
                $format = 'M';
                break;
            case 'n':
            case 'int':
            case 'number':
                $format = 'n';
                break;
            case 'm':
            case 'default':
            default:
                $format = 'm';
        }
        return date($format, (int) $timestamp);
    }
    public static function convertToNextDay($timestamp = '', $format = '')
    {
        $timestamp = DatesTimes::convertDateTimeWith(\DateTime::ATOM, $timestamp);
        $date = new \DateTime($timestamp);
        $date->modify('+1 day');
        switch ($format) {
            case 'name':
                return $date->format('l');
            case 'short_name':
                return $date->format('D');
            case 'number':
                return $date->format('d');
            case 'short_date':
                return $date->format('m/d/Y');
            case 'atom':
                return $date->format(\DateTime::ATOM);
            case '':
            case 'timestamp':
                return DatesTimes::changeTimestampToMidnight($date->getTimestamp());
            default:
                if (DatesTimes::isValidDateFormat($format)) {
                    return $date->format($format);
                } else {
                    return DatesTimes::changeTimestampToMidnight($date->getTimestamp());
                }
        }
    }
    public static function convertToNextMonth($timestamp = '', $format = '')
    {
        $timestamp = DatesTimes::convertDateTimeWith(\DateTime::ATOM, $timestamp);
        $date = new \DateTime($timestamp);
        $date->modify('+1 month');
        switch ($format) {
            case 'name':
                return $date->format('F');
            case 'short_name':
                return $date->format('M');
            case 'number':
                return $date->format('m');
            case 'short_date':
                return $date->format('m/d/Y');
            case 'atom':
                return $date->format(\DateTime::ATOM);
            case 'timestamp':
            default:
                return DatesTimes::changeTimestampToMidnight($date->getTimestamp());
        }
    }
    public static function convertToNextWeek($timestamp = '')
    {
        $timestamp = DatesTimes::convertDateTimeWith(\DateTime::ATOM, $timestamp);
        $date = new \DateTime($timestamp);
        $date->modify('+1 week');
        return $date->format('W');
    }
    public static function convertToNextYear($timestamp = '', $format = '')
    {
        $timestamp = DatesTimes::convertDateTimeWith(\DateTime::ATOM, $timestamp);
        $date = new \DateTime($timestamp);
        $date->modify('+1 year');
        switch ($format) {
            case 'iso':
                return $date->format('o');
            case 'leap':
                return $date->format('L');
            case 'number':
                return $date->format('Y');
            case 'short_date':
                return $date->format('m/d/Y');
            case 'atom':
                return $date->format(\DateTime::ATOM);
            case '':
            case 'timestamp':
                return DatesTimes::changeTimestampToMidnight($date->getTimestamp());
            default:
                if (DatesTimes::isValidDateFormat($format)) {
                    return $date->format($format);
                } else {
                    return DatesTimes::changeTimestampToMidnight($date->getTimestamp());
                }
        }
    }
    public static function convertToPreviousDay($timestamp = '', $format = '')
    {
        $timestamp = DatesTimes::convertDateTimeWith(\DateTime::ATOM, $timestamp);
        $date = new \DateTime($timestamp);
        $date->modify('-1 day');
        switch ($format) {
            case 'name':
                return $date->format('l');
            case 'short_name':
                return $date->format('D');
            case 'number':
                return $date->format('d');
            case 'short_date':
                return $date->format('m/d/Y');
            case 'atom':
                return $date->format(\DateTime::ATOM);
            case '':
            case 'timestamp':
                return DatesTimes::changeTimestampToMidnight($date->getTimestamp());
            default:
                if (DatesTimes::isValidDateFormat($format)) {
                    return $date->format($format);
                } else {
                    return DatesTimes::changeTimestampToMidnight($date->getTimestamp());
                }
        }
    }
    public static function convertToPreviousMonth($timestamp = '', $format = '')
    {
        $timestamp = DatesTimes::convertDateTimeWith(\DateTime::ATOM, $timestamp);
        $date = new \DateTime($timestamp);
        $date->modify('-1 month');
        switch ($format) {
            case 'name':
                return $date->format('F');
            case 'short_name':
                return $date->format('M');
            case 'number':
                return $date->format('m');
            case 'short_date':
                return $date->format('m/d/Y');
            case 'atom':
                return $date->format(\DateTime::ATOM);
            case 'timestamp':
            default:
                return DatesTimes::changeTimestampToMidnight($date->getTimestamp());
        }
    }
    public static function convertToPreviousWeek($timestamp = '')
    {
        $timestamp = DatesTimes::convertDateTimeWith(\DateTime::ATOM, $timestamp);
        $date = new \DateTime($timestamp);
        $date->modify('-1 week');
        return $date->format('W');
    }
    public static function convertToPreviousYear($timestamp = '', $format = '')
    {
        $timestamp = DatesTimes::convertDateTimeWith(\DateTime::ATOM, $timestamp);
        $date = new \DateTime($timestamp);
        $date->modify('-1 year');
        switch ($format) {
            case 'iso':
                return $date->format('o');
            case 'leap':
                return $date->format('L');
            case 'number':
                return $date->format('Y');
            case 'short_date':
                return $date->format('m/d/Y');
            case 'atom':
                return $date->format(\DateTime::ATOM);
            case '':
            case 'timestamp':
                return DatesTimes::changeTimestampToMidnight($date->getTimestamp());
            default:
                if (DatesTimes::isValidDateFormat($format)) {
                    return $date->format($format);
                } else {
                    return DatesTimes::changeTimestampToMidnight($date->getTimestamp());
                }
        }
    }
    public static function convertToShortDate($timestamp = '')
    {
        $timestamp = DatesTimes::convertToUnixTimestamp($timestamp);
        return date("m/d/Y", (int) $timestamp);
    }
    public static function convertToShortDateTime($timestamp = '')
    {
        $timestamp = DatesTimes::convertToUnixTimestamp($timestamp);
        return date("m/d/Y g:i a", (int) $timestamp);
    }
    public static function convertToSqlTimestamp($timestamp = '')
    {
        $timestamp = DatesTimes::convertToUnixTimestamp($timestamp);
        return date("Y-m-d H:i:s", (int) $timestamp);
    }
    public static function convertToTime($timestamp = '')
    {
        $timestamp = DatesTimes::convertToUnixTimestamp($timestamp);
        return date ("H:i:s",  $timestamp);
    }
    public static function convertToTimeFromString($timestring = '')
    {
        if (DatesTimes::isUnixTimestamp($timestring)) {
            $unix_timestamp = $timestring;
        } else {
            $unix_timestamp = $timestring == '' ? time() : strtotime($timestring);
        }
        return date ("H:i:s",  (int) $unix_timestamp);
    }
    public static function convertToTimeWithTz($timestamp = '', $timezone = '')
    {
        $time_format = 'H:i:s e';
        return DatesTimes::convertDateTimeWith($time_format, $timestamp, $timezone);
    }
    public static function convertToTimeWithTzOffset($timestamp = '', $timezone = '')
    {
        $time_format = 'H:i:sP';
        return DatesTimes::convertDateTimeWith($time_format, $timestamp, $timezone);
    }
    public static function convertToTimeWith($time_format = '', $timestamp = '', $timezone = '')
    {
        $time_format = $time_format == '' ? "H:i:s" : $time_format;
        $timezone = $timezone = '' ? date('e') : $timezone;
        return DatesTimes::convertDateTimeWith($time_format, $timestamp, $timezone);
    }
    public static function getTomorrow()
    {
        return DatesTimes::convertToNextDay(date('m/d/Y'), 'short_date');
    }
    public static function convertToUnixTimestamp($timestamp = '')
    {
        $timestamp = $timestamp == '' ? time() : $timestamp;
        return (DatesTimes::isUnixTimestamp($timestamp)
            ? (int) $timestamp
            : strtotime($timestamp));
    }
    public static function convertToWeek($timestamp = '')
    {
        $timestamp = DatesTimes::convertToUnixTimestamp($timestamp);
        return date('W', (int) $timestamp);
    }
    public static function convertToYear($timestamp = '')
    {
        $timestamp = DatesTimes::convertToUnixTimestamp($timestamp);
        return date('Y', (int) $timestamp);
    }
    public static function getYesterday()
    {
        return DatesTimes::convertToPreviousDay(date('m/d/Y'), 'short_date');
    }
    public static function convertToYmd($timestamp = '')
    {
        $timestamp = DatesTimes::convertToUnixTimestamp($timestamp);
        return date("Ymd", (int) $timestamp);
    }
    public static function isUnixTimestamp($value = '')
    {
        if ($value == '') {
            return false;
        }
        switch (strtotime($value)) {
            case '':
                return (date('m/d/Y', (int) $value) != '' ? true : false);
            case false:
                return false;
            default:
                return false;
        }
    }
    public static function isValidDateFormat($date_format = '')
    {
        $a_date_info = date_parse_from_format($date_format, date($date_format));
        if ($a_date_info['error_count'] > 0) {
            return false;
        } elseif ($a_date_info['year']
                + $a_date_info['month']
                + $a_date_info['day']
                + $a_date_info['hour']
                + $a_date_info['minute']
                + $a_date_info['second']
                + $a_date_info['fraction'] == 0)  {
            return false;
        } else {
            return true;
        }
    }
    public static function isValidTimezone($timezone = '')
    {
        if ($timezone == '') {
            return false;
        }
        try {
            $valid = new \DateTimeZone($timezone);
            if ($valid) {
                return true;
            }
        }
        catch (\Exception $e) {
            return false;
        }
        return false;
    }
}
