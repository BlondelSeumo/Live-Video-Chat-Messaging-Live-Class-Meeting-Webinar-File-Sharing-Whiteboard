<?php

namespace App\Helpers;

use App\Helpers\ArrHelper;
use Illuminate\Support\Arr;

class ListHelper
{
    /**
     * Get config lists from json
     * @param array $list
     */
    public static function getConfigLists($types = array()) : array
    {
        $lists = ArrHelper::getVar('list');

        $data = array();
        foreach ($types as $type) {
            if (Arr::has($lists, 'config.'.$type)) {
                $list = Arr::get($lists, 'config.'.$type, []);
                $data[$type] = ArrHelper::getSelectList($list);
            }
        }

        return $data;
    }

    /**
     * Get currency list
     */
    public static function getCurrencies() : array
    {
        return ArrHelper::getVar('currencies');
    }

    /**
     * Get currency by name
     * @param string $name
     */
    public static function getCurrencyByName($name = null) : array
    {
        return Arr::first(self::getCurrencies(), function($value, $key) use($name) {
            return Arr::get($value, 'name') === $name;
        }, []);
    }

    /**
     * Get country list
     */
    public static function getCountries() : array
    {
        $countries = ArrHelper::getVar('countries');
        return ArrHelper::getSelectList($countries, true);
    }

    /**
     * Get country by id
     * @param integer $id
     */
    public static function getCountryById($id = null) : array
    {
        return Arr::first(self::getCountries(), function($value, $key) use($id) {
            return Arr::get($value, 'uuid') === $id;
        }, []);
    }

    /**
     * Get country by name
     * @param string $name
     */
    public static function getCountryByName($name = null) : array
    {
        return Arr::first(self::getCountries(), function($value, $key) use($name) {
            return Arr::get($value, 'name') === $name;
        }, []);
    }

    /**
     * Get timezone list
     */
    public static function getTimezones() : array
    {
        $timezones = ArrHelper::getVar('timezones');
        return ArrHelper::getSelectList($timezones);
    }

    /**
     * Get timezone by name
     * @param string $name
     */
    public static function getTimezoneByName($name = null) : array
    {
        return Arr::first(self::getTimezones(), function($value, $key) use($name) {
            return Arr::get($value, 'uuid') === $name;
        }, []);
    }

    /**
     * Get list by id
     * @param string $id
     * @param string $key
     * @param string $type
     */
    public static function getListById($key, $id = null, $type = 'general') : array
    {
        return Arr::first(ArrHelper::getTransList($key, $type), function($value, $key) use($id) {
            return Arr::get($value, 'uuid') === $id;
        }, []);
    }
}
