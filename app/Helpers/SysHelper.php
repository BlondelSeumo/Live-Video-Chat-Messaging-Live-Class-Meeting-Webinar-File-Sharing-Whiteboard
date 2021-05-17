<?php
namespace App\Helpers;

class SysHelper
{
    /**
     * Write to env file
     * @param  array  $data
     */
    public static function setEnv($data = array()) : bool
    {
        foreach ($data as $key => $value) {
            if (env($key) === $value) {
                unset($data[$key]);
            }
        }

        if (!count($data)) {
            return false;
        }

        // write only if there is change in content

        $env = file_get_contents(base_path() . '/.env');
        $env = explode("\n", $env);
        foreach ((array)$data as $key => $value) {
            foreach ($env as $env_key => $env_value) {
                $entry = explode("=", $env_value, 2);
                if ($entry[0] === $key) {
                    $env[$env_key] = $key . "=" . (is_string($value) ? '"'.$value.'"' : $value);
                } else {
                    $env[$env_key] = $env_value;
                }
            }
        }
        $env = implode("\n", $env);
        file_put_contents(base_path() . '/.env', $env);
        return true;
    }

    /**
     * Get application variable
     * @param  string $var
     */
    public static function getApp($var = null) : ?string
    {
        if (! $var) {
            return null;
        }

        $app = \Storage::exists('.app') ? \Storage::get('.app') : '';
        $app = explode("\n", str_replace("\r", "", $app));

        foreach ($app as $string) {
            $string = explode("=", trim($string));
            if (array_first($string) === $var) {
                return array_last($string);
            }
        }

        return null;
    }

    /**
     * Set application variable
     * @param string $var
     */
    public static function setApp($var = array()) : void
    {
        $app = \Storage::exists('.app') ? \Storage::get('.app') : '';
        $app = explode("\n", str_replace("\r", "", $app));

        $latest = $app;

        foreach ($var as $key => $value) {
            $matched = 0;
            foreach ($app as $index => $string) {
                $string = explode("=", trim($string));
                if (array_first($string) === $key) {
                    $string[1] = $value;
                    $matched++;
                }

                if (array_first($string)) {
                    $latest[$index] = array_first($string).'='.array_last($string);
                }
            }

            if (! $matched) {
                $latest[] = $key.'='.$value;
            }
        }

        \Storage::put('.app', implode("\n", $latest));
    }

    /**
     * If app is in test mode
     */
    public static function isTestMode() : bool
    {
        return env('APP_MODE') === 'test' ? true : false;
    }

    /**
     * Get maximum post size of server
     */
    public static function getPostMaxSize() : int
    {
        if (is_numeric($postMaxSize = ini_get('post_max_size'))) {
            return (int) $postMaxSize;
        }

        $metric = strtoupper(substr($postMaxSize, -1));
        $postMaxSize = (int) $postMaxSize;

        switch ($metric) {
            case 'K':
                return $postMaxSize * 1024;
            case 'M':
                return $postMaxSize * 1048576;
            case 'G':
                return $postMaxSize * 1073741824;
            default:
                return $postMaxSize;
        }
    }

    /**
     * Format size units
     * @param numeric $bytes
     */
    public static function formatSizeUnits($bytes) : string
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

    /**
     * Format currency
     * @param numeric $amount
     */
    public static function formatCurrency($amount) : float
    {
        return round($amount, config('config.system.default_currency.decimal', 2));
    }

    /**
     * Format percentage
     * @param numeric $value
     */
    public static function formatPercentage($value) : float
    {
        return round($value, 2);
    }

    /**
     * Check if app is connected to internet
     */
    public static function isConnected() : bool
    {
        $connected = @fsockopen("www.google.com", 80);
        if ($connected) {
            fclose($connected);
            return true;
        }

        return false;
    }
}
