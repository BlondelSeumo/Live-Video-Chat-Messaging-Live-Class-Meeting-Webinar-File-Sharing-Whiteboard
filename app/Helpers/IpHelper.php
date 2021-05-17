<?php
namespace App\Helpers;

class IpHelper
{
    /**
     * Check if connected to internet
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

    /**
     * Check if host is IP
     *
     * @param string $host
     */
    public static function isIp($host) : bool
    {
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return true;
        }

        return false;
    }

    /**
     * Check if IP exists in range
     * @param collection $ips
     * @param string $client_ip
     */
    public static function isIpInRange($ips, $client_ip = '') : bool
    {
        $client_ip = (! $client_ip) ? self::getClientIp() : $client_ip;

        $allowedIps = array();
        foreach ($ips as $ip) {
            if ($ip->endIp) {
                $allowedIps[] = $ip->startIp.'-'.$ip->endIp;
            } else {
                $allowedIps[] = $ip->startIp;
            }
        }

        foreach ($allowedIps as $allowedIp) {
            if (strpos($allowedIp, '*')) {
                $range = [
                    str_replace('*', '0', $allowedIp),
                    str_replace('*', '255', $allowedIp)
                ];
                if (self::ipExistsInRange($range, $client_ip)) {
                    return true;
                }
            } elseif (strpos($allowedIp, '-')) {
                $range = explode('-', str_replace(' ', '', $allowedIp));
                if (self::ipExistsInRange($range, $client_ip)) {
                    return true;
                }
            } else {
                if (ip2long($allowedIp) === ip2long($client_ip)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check if Ip in range
     * @param array $range
     * @param ip $ip
     */
    public static function ipExistsInRange(array $range, $ip) : bool
    {
        if (ip2long($ip) >= ip2long($range[0]) && ip2long($ip) <= ip2long($range[1])) {
            return true;
        }
        return false;
    }

    /**
     * Get client remote IP address
     */
    public static function getRemoteIPAddress() : ?string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return array_key_exists('REMOTE_ADDR', $_SERVER) ? $_SERVER['REMOTE_ADDR'] : null;
    }

    /**
     * Get client local Ip address
     */
    public static function getClientIp() : string
    {
        $ips = self::getRemoteIPAddress();
        $ips = explode(',', $ips);
        return !empty($ips[0]) ? $ips[0] : \Request::getClientIp();
    }
}
