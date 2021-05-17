<?php
namespace App\Helpers;

use Carbon\Carbon;

class CalHelper
{
    /**
     * Validate a date
     * @param string $date
     */
    public static function validateDate($date) : bool
    {
        try {
            \Carbon\Carbon::parse($date);
        } catch (\Exception $e) {
            return false;
        }
        
        return true;
    }

    /**
     * Get date difference
     * @param string $date1
     * @param string $date2
     * @param integer $increment
     */
    public static function dateDiff($date1, $date2, $increment = 0) : int
    {
        $date = Carbon::parse($date1);

        return abs($date->diffInDays(Carbon::parse($date2)));
    }

    /**
     * Get age from date
     * @param string date
     */
    public static function getAge($date) : array
    {
        $age = Carbon::parse($date)->diff(Carbon::now());

        return array(
            'years' => $age->y,
            'months' => $age->m,
            'days' => $age->d,
        );
    }

    /**
     * Get start of given date
     * @param date $date
     */
    public static function startOfDate($date) : string
    {
        return Carbon::parse($date)->startOfDay()->toDateTimeString();
    }

    /**
     * Get end of given date
     * @param date $date
     */
    public static function endOfDate($date) : string
    {
        return Carbon::parse($date)->endOfDay()->toDateTimeString();
    }

    /**
     * Get humanize date format
     * @return string
     */
    public static function getDateFormat() : string
    {
        return 'd-m-Y';
    }

    /**
     * Get humanize time format
     */
    public static function getTimeFormat() : string
    {
        return 'h:i A';
    }

    /**
     * Get system date format
     */
    public static function getSysDateFormat() : string
    {
        return 'Y-m-d';
    }

    /**
     * Get system time format
     */
    public static function getSysTimeFormat() : string
    {
        return 'h:i:s';
    }

    /**
     * Get humanize date time format
     */
    public static function getDateTimeFormat() : string
    {
        return self::getDateFormat().' '.self::getTimeFormat();
    }

    /**
     * Get system date time format
     */
    public static function getSysDateTimeFormat() : string
    {
        return self::getSysDateFormat().' '.self::getSysTimeFormat();
    }

    /**
     * Get today dat
     */
    public static function today() : string
    {
        return Carbon::today()->toDateString();
    }

    /**
     * Convert to system date
     * @param date $date
     */
    public static function toDate($date) : ?string
    {
        return ($date) ? Carbon::parse($date)->toDateString() : null;
    }

    /**
     * Convert to system time
     * @param time $time
     */
    public static function toTime($time) : ?string
    {
        return ($time) ? Carbon::parse($time)->toTimeString() : null;
    }

    /**
     * Convert to system date time
     * @param datetime $datetime
     */
    public static function toDateTime($datetime) : ?string
    {
        // return ($datetime) ? Carbon::parse($datetime)->timezone(config('config.display_timezone'))->toDateTimeString() : null;
        return ($datetime) ? Carbon::parse($datetime)->toDateTimeString() : null;
    }

    /**
     * Convert date to user defined date format
     * @param date $date
     */
    public static function showDate($date) : ?string
    {
        return ($date) ? date(self::getDateFormat(), strtotime($date)) : null;
    }

    /**
     * Convert time to user defined time format
     * @param time $time
     */
    public static function showTime($time) : ?string
    {
        return ($time) ? date((self::getTimeFormat()), strtotime($time)) : null;
    }

    /**
     * Convert date to user defined date time format
     * @param time $time
     */
    public static function showDateTime($time) : ?string
    {
        return ($time) ? date(self::getDateFormat().','.(self::getTimeFormat()), strtotime($time)) : null;
    }

    /**
     * Convert datetime to UTC before store
     *
     * @param datetime $datetime
     */
    public static function storeDateTime($datetime)
    {
        if (! $datetime) {
            return null;
        }

        if (! \Auth::check()) {
            return $datetime;
        }

        return Carbon::parse($datetime, \Auth::user()->timezone)->timezone(config('app.timezone'));
    }
    
    /**
     * Get random date between two dates
     *
     * @param $date $start_date
     * @param $date $end_date
     * @param string $format
     */
    public static function randomDate($start_date, $end_date, $format = 'Y-m-d H:i:s')
    {
        $min = strtotime($start_date);
        $max = strtotime($end_date);

        $value = mt_rand($min, $max);

        return date($format, $value);
    }
}
