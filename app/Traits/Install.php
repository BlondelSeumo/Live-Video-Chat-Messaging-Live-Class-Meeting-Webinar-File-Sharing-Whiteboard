<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

trait Install
{
    /**
     * Used to check extension enabled or not
     */
    public function check($boolean, $message, $help = '', $fatal = false)
    {
        if ($boolean) {
            return array('type' => 'success','message' => $message);
        } else {
            return array('type' => 'error', 'message' => $help);
        }
    }

    /**
     * Used to compare version of packages
     */
    public function my_version_compare($ver1, $ver2, $operator = null)
    {
        $p = '#(\.0+)+($|-)#';
        $ver1 = preg_replace($p, '', $ver1);
        $ver2 = preg_replace($p, '', $ver2);
        return isset($operator) ?
            version_compare($ver1, $ver2, $operator) :
            version_compare($ver1, $ver2);
    }
    
    /**
     * Used to check whether pre requisites are fulfilled or not and returns array of success/error type with message
     */
    public function installPreRequisite()
    {
        $server[] = $this->check((dirname($_SERVER['REQUEST_URI']) != '/' && str_replace('\\', '/', dirname($_SERVER['REQUEST_URI'])) != '/'), 'Installation directory is valid.', 'Please use root directory or point your sub directory to domain/subdomain to install.', true);
        $server[] = $this->check($this->my_version_compare(phpversion(), '7.4.0', '>='), sprintf('Min PHP version 7.4.0 (%s)', 'Current Version '. phpversion()), 'Current Version '.phpversion(), true);
        $server[] = $this->check(extension_loaded('fileinfo'), 'Fileinfo PHP extension enabled.', 'Install and enable Fileinfo extension.', true);
        $server[] = $this->check(extension_loaded('openssl'), 'OpenSSL PHP extension enabled.', 'Install and enable OpenSSL extension.', true);
        $server[] = $this->check(extension_loaded('tokenizer'), 'Tokenizer PHP extension enabled.', 'Install and enable Tokenizer extension.', true);
        $server[] = $this->check(extension_loaded('mbstring'), 'Mbstring PHP extension enabled.', 'Install and enable Mbstring extension.', true);
        $server[] = $this->check(extension_loaded('zip'), 'Zip archive PHP extension enabled.', 'Install and enable Zip archive extension.', true);
        $server[] = $this->check(class_exists('PDO'), 'PDO is installed.', 'Install PDO (mandatory for Eloquent).', true);
        // $server[] = $this->check(extension_loaded('curl'), 'CURL '.Arr::get(curl_version(), 'version').' is installed.', 'Install and enable CURL.', true);
        if (extension_loaded('curl')) {
            $server[] = $this->check($this->my_version_compare(Arr::get(curl_version(), 'version'), '7.60.0', '>='), 'CURL version is up-to-date', 'Upgrade CURL version to atleast 7.60.0', true);
        } else {
            $server[] = 'Install and enable CURL v7.60.0.';
        }
        $server[] = $this->check(ini_get('allow_url_fopen'), 'allow_url_fopen is on.', 'Turn on allow_url_fopen.', true);

        $folder[] = $this->check(is_writable("../storage/framework"), 'Folder /storage/framework is writable', 'Folder /storage/framework is not writable', true);
        $folder[] = $this->check(is_writable("../storage/logs"), 'Folder /storage/logs is writable', 'Folder /storage/logs is not writable', true);
        $folder[] = $this->check(is_writable("../bootstrap/cache"), 'Folder /bootstrap/cache is writable', 'Folder /bootstrap/cache is not writable', true);

        return compact('server', 'folder');
    }

    public function checkDbVersion($version)
    {
        $mysql_required_version = '8.0.0';
        $mariadb_required_version = '10.2.7';

        if (Str::contains(strtolower($version), 'maria')) {
            $db = explode('-', $version);
            $db = $db[0] ?? '1.0.0';

            if (! $this->my_version_compare($db, $mariadb_required_version, '>=')) {
                throw ValidationException::withMessages(['message' => 'Please install MariaDB version >= ' . $mariadb_required_version]);
            }
        } else if (! $this->my_version_compare($version, $mysql_required_version, '>=')) {
            throw ValidationException::withMessages(['message' => 'Please install MySQL version >= ' . $mysql_required_version]);
        }
    }
}