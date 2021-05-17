<?php

namespace App\Repositories\Config;

use App\Enums\MeetingStatus;
use App\Helpers\ArrHelper;
use App\Helpers\SysHelper;
use App\Helpers\ListHelper;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\Config\Config;
use App\Models\Meeting;
use Illuminate\Validation\ValidationException;
use App\Repositories\Config\LocaleRepository;
use Minishlink\WebPush\VAPID;

class ConfigRepository
{
    protected $config;
    protected $locale;

    /**
     * Instantiate a new instance
     * @return void
     */
    public function __construct(
        Config $config,
        LocaleRepository $locale
    ) {
        $this->config = $config;
        $this->locale = $locale;
    }

    /**
     * Get pre requisite of config
     */
    public function getPreRequisite() : array
    {
        $types = snake_case(request('types'));

        $types = (! is_array($types)) ? explode(",", $types) : $types;

        $data = ListHelper::getConfigLists($types);

        if (in_array('countries', $types)) {
            $data['countries'] = ListHelper::getCountries();
        }

        if (in_array('currencies', $types)) {
            $data['currencies'] = ListHelper::getCurrencies();
        }

        if (in_array('timezones', $types)) {
            $data['timezones'] = ListHelper::getTimezones();
        }

        if (in_array('frequencies', $types)) {
            $data['frequencies'] = ArrHelper::getTransList('frequencies', 'general');
        }

        if (in_array('locales', $types)) {
            $data['locales'] = $this->locale->getLocales();
        }

        return $data;
    }

    /**
     * List all config variables
     */
    public function listAll() : array
    {
        return \Cache::remember('query_list_all_config', 60 * 60, function () {
            return $this->config->all()->pluck('value', 'name')->all();
        });
    }

    /**
     * Get config variables
     */
    public function getConfig() : array
    {
        if (! SysHelper::getApp('INSTALLED')) {
            return array('failed_install' => true);
        }

        $db_config = $this->listAll();

        if (! \Auth::check()) {
            return $this->getPublicConfig($db_config, false);
        } else if (config('config.auth.two_factor_security') && session()->exists('2fa')) {
            return $this->getPublicConfig($db_config, true, true);
        }

        $app_config = $this->getAppConfig($db_config);
        $app_config = $this->maskPrivateConfig($app_config);
        $app_config['authenticated'] = true;
        $app_config['license'] = SysHelper::getApp('ACCESS_CODE') ? true : false;

        $ice = Arr::get($db_config, 'ice', []);
        $ice_configs = $this->getIceConfig($ice);
        $app_config['ice'] = Arr::get($ice_configs, 'ice', []);

        $app_config['ice_servers'] = Arr::get($ice_configs, 'ice_servers', []);

        return $this->addGeneralConfig($app_config);
    }

    /**
     * Get public config
     *
     * @param array $public_config
     * @param array $db_config
     * @param bool $authenticated
     * @param bool $two_factor
     */
    private function getPublicConfig($db_config = array(), $authenticated = false, $two_factor = false) : array
    {
        $config = ArrHelper::getVar('config');
        $default_config = Arr::get($config, 'default_config', []);
        $public_config  = Arr::get($config, 'public_config', []);

        $configs = array();
        foreach ($public_config as $key => $config) {
            foreach ($config as $item) {
                $configs[$key][$item] = ! is_null(Arr::get($db_config, $key.'.'.$item)) ? Arr::get($db_config, $key.'.'.$item) : Arr::get($default_config, $key.'.'.$item);
            }
        }

        $configs['authenticated'] = $authenticated;
        $configs['auth']['two_factor_security_pending'] = $two_factor;

        if (request('meeting') && request()->boolean('pam')) {
            $meeting = Meeting::filterByUuid(request('meeting'))->first();

            if ($meeting && $meeting->getMeta('is_pam') && $meeting->getMeta('status') !== MeetingStatus::CANCELLED && $meeting->getMeta('status') !== MeetingStatus::ENDED) {
                $ice = Arr::get($db_config, 'ice', []);
                $ice_configs = $this->getIceConfig($ice);
                // $configs['ice'] = Arr::get($ice_configs, 'ice', []);
                $configs['ice_servers'] = Arr::get($ice_configs, 'ice_servers', []);
                $configs['signal'] = Arr::get($db_config, 'signal', []);
                $configs['pusher'] = Arr::get($db_config, 'pusher', []);
                $configs['pusher']['pusher_app_id'] = config('default.private_mask');
                $configs['pusher']['pusher_app_secret'] = config('default.private_mask');
                $configs['pusher']['debug_mode'] = false;
                $configs['meeting'] = Arr::get($db_config, 'meeting', []);
                $configs['meeting']['debug_mode'] = false;
            }
        }

        return $this->addGeneralConfig($configs);
    }

    /**
     * Get app config
     *
     * @param array $db_config
     */
    private function getAppConfig($db_config = array()) : array
    {
        $config = ArrHelper::getVar('config');
        $default_config = Arr::get($config, 'default_config', []);

        return collect($default_config)->transform(function ($configs, $key) use ($db_config) {
            return array_merge($configs, Arr::get($db_config, $key, []));
        })->all();
    }

    /**
     * Mask private config
     *
     * @param array $configs
     */
    private function maskPrivateConfig($configs = array()) : array
    {
        $config = ArrHelper::getVar('config');
        $private_config = Arr::get($config, 'private_config', []);

        return collect($configs)->transform(function ($config, $key) use ($private_config) {
            return collect($config)->transform(function ($value, $name) use ($private_config, $key) {
                $is_private = in_array($name, Arr::get($private_config, $key, [])) ? true : false;
                return $is_private && $value ? config('default.private_mask')  : $value;
            });
        })->all();
    }

    private function getIceConfig($ice = []) : array
    {
        if (! $ice) {
            return [];
        }

        $servers = Arr::get($ice, 'servers', []);

        $ice = array();
        $ice_servers = array();
        foreach ($servers as $server) {
            $item = array();
            $ice_servers_item = array();
            foreach ($server as $key => $value) {
                if (in_array($key, ['requires_credential', 'expirable_credentials'])) {
                    $item[$key] = $value ? true : false;
                } else if ($key === 'secret') {
                    $item[$key] = config('default.private_mask');
                } else {
                    $item[$key] = $value;
                }
            }

            $ice[] = $item;

            $urls = Arr::get($server, 'urls');
            $urls = preg_replace('/\s+/', '', $urls);

            if(strpos($urls, ',') !== false) {
                $urls = explode(",", $urls);
            }

            if (Arr::get($server, 'requires_credential')) {
                if (Arr::get($server, 'expirable_credentials')) {
                    $user = optional(\Auth::user())->username ? : Str::random(6);
                    $expire_time_unix_epoch = time() + (Arr::get($server, 'expires_in') + Arr::get($server, 'time_difference')) ;
                    $username = $expire_time_unix_epoch . ":" . $user ;
                    $hmac_sha1 = hash_hmac("sha1", $username, Arr::get($server, 'secret'), true) ;
                    $credential = base64_encode($hmac_sha1) ;

                    $ice_servers[] = array(
                        'urls' => $urls,
                        'username' => $username,
                        'credential' => $credential
                    );
                } else {
                    $ice_servers[] = array(
                        'urls' => $urls,
                        'username' => Arr::get($server, 'username'),
                        'credential' => Arr::get($server, 'credential')
                    );
                }
            } else {
                $ice_servers[] = array('urls' => $urls);
            }
        }

        return array(
            'ice'         => array('servers' => $ice),
            'ice_servers' => $ice_servers
        );
    }

    /**
     * Get general config
     * @param array $app
     */
    private function addGeneralConfig($app = array()) : array
    {
        $system_currencies = ArrHelper::getVar('currencies');

        $app['system']['version'] = SysHelper::getApp('VERSION');
        $app['system']['mode'] = (env('APP_MODE') == 'test') ? 0 : 1;
        $app['system']['paginations'] = ArrHelper::getList('paginations', 'config');
        $app['auth']['social_login_providers'] = $this->getActiveSocialLoginProviders();
        $app['system']['post_max_size'] = SysHelper::getPostMaxSize();
        $app['system']['currency'] = ArrHelper::searchByKey($system_currencies, 'name', Arr::get($app, 'system.currency'));
        $app['system']['locales'] = $this->locale->getLocales();

        return $app;
    }

    /**
     * Get active social login providers
     */
    private function getActiveSocialLoginProviders() : array
    {
        return array_values(collect(ArrHelper::getList('social_login_providers', 'config'))->filter(function ($item) {
            return config('config.auth.'.$item) ? true : false;
        })->all());
    }

    /**
     * Set default configuration
     */
    public function setDefault() : void
    {
        $config = ArrHelper::getVar('config');
        $acl = ArrHelper::getVar('acl');

        config(['default' => array_merge($config, $acl)]);

        if (! SysHelper::getApp('INSTALLED')) {
            return;
        }

        $db_config = $this->listAll();

        config(['config' =>  $this->getAppConfig($db_config)]);
        config(['config.system.default_currency' => ListHelper::getCurrencyByName(config('config.system.currency'))]);

        $this->initSystemDefault();

        $this->initSocialLogin();

        $this->initMailConfig();

        $this->initNotificationConfig();

        if (\Auth::check()) {
            config([
                'config.display_timezone' => \Auth::user()->timezone ?? config('config.system.timezone'),
                'config.system.locale' => \Auth::user()->getPreference('system.locale') ?? config('config.system.locale')
            ]);
        }

        config([
            'app.name' => config('config.basic.org_name'),
            'app.locale' => config('config.system.locale')
        ]);

        if (request()->query('locale')) {
            \App::setLocale(request()->query('locale'));
        } else {
            \App::setLocale(config('app.locale', 'en'));
        }
    }

    /**
     * Set default system configuration
     */
    private function initSystemDefault() : void
    {
        config(['session.lifetime' => config('config.auth.session_lifetime') ?: 1440]);
        config(['media-library.max_file_size' => SysHelper::getPostMaxSize()]);
    }

    /**
     * Set social login configuration
     */
    private function initSocialLogin() : void
    {
        foreach ($this->getActiveSocialLoginProviders() as $provider) {
            config([
                'services.'.$provider.'.client_id' => config('config.auth.'.$provider.'_client_id'),
                'services.'.$provider.'.client_secret' => config('config.auth.'.$provider.'_client_secret'),
                'services.'.$provider.'.redirect' => url('/auth/login/'.$provider.'/callback')
            ]);
        }
    }

    private function initMailConfig() : void
    {
        config([
            'mail.from.address' => config('config.mail.from_address'),
            'mail.from.name'    => config('config.mail.from_name'),
            'mail.default'      => config('config.mail.driver')
        ]);

        if (config('config.mail.driver') === 'mailgun') {
            config([
                'services.mailgun.domain'   => config('config.mail.mailgun_domain'),
                'services.mailgun.secret'   => config('config.mail.mailgun_secret'),
                'services.mailgun.endpoint' => config('config.mail.mailgun_endpoint')
            ]);
        } else if (config('config.mail.driver') === 'smtp') {
            config([
                'mail.mailers.smtp.host'       => config('config.mail.smtp_host'),
                'mail.mailers.smtp.port'       => config('config.mail.smtp_port'),
                'mail.mailers.smtp.encryption' => config('config.mail.smtp_encryption'),
                'mail.mailers.smtp.username'   => config('config.mail.smtp_username'),
                'mail.mailers.smtp.password'   => config('config.mail.smtp_password')
            ]);
        }
    }

    private function initNotificationConfig() : void
    {
        config([
            'webpush.vapid.public_key' => config('config.notification.vapid_public_key'),
            'webpush.vapid.private_key' => config('config.notification.vapid_private_key')
        ]);
    }

    /**
     * Store config variables
     * @param array $params
     */
    public function store() : void
    {
        if (! request()->has('type')) {
            throw ValidationException::withMessages(['message' => __('general.invalid_action')]);
        }

        $method = 'set'.title_case(request('type')).'Config';

        if (method_exists($this, $method)) {
            $this->$method();
        }
    }

    /**
     * Clear config cache
     */
    private function clearCache() : void
    {
        cache()->forget('query_list_all_config');
    }

    /**
     * Set basic config
     */
    private function setBasicConfig()
    {
        request()->validate([
            'org_name'            => 'required',
            'org_address_line1'   => 'required',
            'org_country'         => 'required',
            'org_email'           => 'required|email',
            'org_foundation_date' => 'required|date'
        ], [], [
            'org_name'            => __('config.basic.org_name'),
            'org_address_line1'   => __('config.basic.org_address_line1'),
            'org_country'         => __('config.basic.org_country'),
            'org_email'           => __('config.basic.org_email'),
            'org_foundation_date' => __('config.basic.org_foundation_date')
        ]);

        $this->storeConfig();
    }

    /**
     * Set system config
     *
     * @param array $params
     */
    private function setSystemConfig()
    {
        if (request()->has('currency')) {
            request()->merge(['currency' => Arr::get(request('currency'), 'name')]);
        }

        $this->storeConfig();

        $this->checkSymlink();
    }

    /**
     * Set social config
     *
     * @param array $params
     */
    private function setSocialConfig($params = array())
    {
        $this->storeConfig();
    }

    /**
     * Set mail config
     *
     * @param array $params
     */
    private function setMailConfig($params = array())
    {
        $rules = [
            'driver' => 'in:'.implode(',', ArrHelper::getList('mail_drivers', 'config')),
            'from_name'    => 'required',
            'from_address' => 'required'
        ];

        if (request('driver') == 'smtp') {
            $rules['smtp_host']       = 'required';
            $rules['smtp_port']       = 'required';
            $rules['smtp_username']   = 'required';
            $rules['smtp_password']   = 'required';
            $rules['smtp_encryption'] = 'in:ssl,tls,'.config('default.private_mask');
        }

        if (request('driver') == 'mailgun') {
            $rules['mailgun_domain']   = 'required';
            $rules['mailgun_secret']   = 'required';
            $rules['mailgun_endpoint'] = 'required';
        }

        request()->validate($rules, [], [
            'from_name'    => __('config.mail.from_name'),
            'from_address' => __('config.mail.from_address')
        ]);

        $this->storeConfig();
    }

    /**
     * Set SMS config
     */
    private function setSMSConfig()
    {
        request()->validate([
            'custom_api_get_url'         => 'required|url',
            'custom_api_sender_id_param' => 'required',
            'max_per_chunk'              => 'required|integer|min:1',
            'custom_api_sender_id'       => 'required',
            'custom_api_receiver_param'  => 'required',
            'custom_api_message_param'   => 'required'
        ], [], [
            'gateway' => __('config.sms.gateway')
        ]);

        $this->storeConfig();
    }

    /**
     * Set auth config
     */
    private function setAuthConfig()
    {
        request()->validate([
            'token_lifetime'                => 'integer|min:1',
            'reset_password_token_lifetime' => 'integer|min:5|max:300',
            'lock_screen_timeout'           => 'required_if:lock_screen,1|integer|min:1|max:60',
            'login_throttle_attempt'        => 'required_if:login_throttle,1|integer|min:2|max:10',
            'login_throttle_timeout'        => 'required_if:login_throttle,1|integer|min:1|max:300',
            'google_client_id'              => 'required_if:google,1',
            'google_client_secret'          => 'required_if:google,1',
            'facebook_client_id'            => 'required_if:facebook,1',
            'facebook_client_secret'        => 'required_if:facebook,1',
            'twitter_client_id'             => 'required_if:twitter,1',
            'twitter_client_secret'         => 'required_if:twitter,1',
            'github_client_id'              => 'required_if:github,1',
            'github_client_secret'          => 'required_if:github,1',
        ], [], [
            'token_lifetime'                => __('config.auth.token_lifetime'),
            'reset_password_token_lifetime' => __('config.auth.reset_password_token_lifetime'),
            'login_throttle_attempt'        => __('config.auth.login_throttle_attempt'),
            'login_throttle_timeout'        => __('config.auth.login_throttle_timeout'),
            'lock_screen_timeout'           => __('config.auth.lock_screen_timeout')
        ]);

        $this->storeConfig();
    }

    private function setPusherConfig() : void
    {
        $this->storeConfig();

        SysHelper::setEnv([
            'PUSHER_APP_ID'      => request('pusher_app_id'),
            'PUSHER_APP_KEY'     => request('pusher_app_key'),
            'PUSHER_APP_SECRET'  => request('pusher_app_secret'),
            'PUSHER_APP_CLUSTER' => request('pusher_app_cluster')
        ]);
    }

    /**
     * Set Chat config
     */
    private function setChatConfig()
    {
        $this->storeConfig();
    }

    /**
     * Set Website config
     */
    private function setWebsiteConfig()
    {
        $this->storeConfig();
    }

    /**
     * Set Signal Server config
     */
    private function setSignalConfig()
    {
        request()->validate([
            'url' => 'nullable|url'
        ]);

        $this->storeConfig();
    }

    /**
     * Set Meeting config
     */
    private function setMeetingConfig()
    {
        $this->storeConfig();
    }

    /**
     * Store configuration
     */
    private function storeConfig() : void
    {
        $config = $this->config->firstOrCreate(['name' => request('type')]);

        $boolean_lists = Arr::get(ArrHelper::getVar('config'), 'bool_config.'.request('type'), []);

        $input = request()->all();

        array_walk_recursive($input, function (&$input, $key) use ($config, $boolean_lists) {
            if ($input == config('default.private_mask')) {
                $input = $config->getValue($key);
            } else if (in_array($key, $boolean_lists)) {
                $input = $input ? true : false;
            }
        });

        request()->merge($input);

        $config->value = array_merge($config->value ? : [], request()->except('type'));
        $config->save();

        $this->clearCache();
    }

    /**
     * Set ui config
     */
    private function setUiConfig()
    {
        $config = $this->config->firstOrCreate(['name' => 'ui']);

        $params = request()->except('type');

        $values = $config->value;
        foreach ($params as $key => $value) {
            if ($value === '' || $value === false || $value === 0 || $value === '0') {
                $values[$key] = false;
            } elseif ($value === true || $value === 1 || $value === "1") {
                $values[$key] = true;
            } else {
                $values[$key] = $value;
            }
        }
        $config->value = $values;
        $config->save();

        $this->clearCache();
    }

    /**
     * Store notification vapid key
     */
    public function setNotificationConfig() : void
    {
        if (config('config.notification.vapid_public_key')) {
            return;
        }

        $keys = VAPID::createVapidKeys();

        $config = Config::firstOrCreate(['name' => 'notification']);
        $value = array(
            'vapid_public_key' => Arr::get($keys, 'publicKey'),
            'vapid_private_key' => Arr::get($keys, 'privateKey')
        );
        $config->value = $value;
        $config->save();

        $this->clearCache();
    }

    /**
     * Set ui config
     */
    private function setIceConfig()
    {
        $config = $this->config->firstOrCreate(['name' => 'ice']);

        $servers = request('servers');

        $values = $config->value;
        $data = array();
        foreach ($servers as $server) {
            $item = array();
            foreach ($server as $key => $value) {
                $item[$key] = (in_array($key, ['requires_credential', 'expirable_credentials'])) ? ($value ? true : false) : $value;
            }

            $data[] = $item;
        }
        $config->value = array('servers' => $data);
        $config->save();

        $this->clearCache();
    }

    /**
     * Get config asset
     */
    private function getAsset() : string
    {
        if (! in_array(request('type'), Config::ASSET_TYPES)) {
            throw ValidationException::withMessages(['message' => __('general.invalid_action')]);
        }

        return str_replace('/storage/', '', config('config.assets.'.request('type')));
    }

    /**
     * Update config asset
     * @param string $asset
     */
    private function updateAsset($asset = null) : Config
    {
        $config = $this->config->firstOrCreate(['name' => 'assets']);
        $value = $config->value;
        $value[request('type')] = $asset;
        if (is_null($asset)) {
            unset($value[request('type')]);
        }
        $config->value = $value;
        $config->save();

        $this->clearCache();

        return $config;
    }

    /**
     * Upload config asset
     */
    public function uploadAsset() : Config
    {
        request()->validate([
            'file' => 'required|image'
        ]);

        $asset = $this->getAsset();

        if ($asset && \Storage::disk('public')->exists($asset)) {
            \Storage::disk('public')->delete($asset);
        }

        $type = request('type');

        $file = \Storage::disk('public')->putFile($type, request()->file('file'));

        return $this->updateAsset('/storage/'.$file);
    }

    /**
     * Remove config asset
     */
    public function removeAsset() : void
    {
        $asset = $this->getAsset();

        $type = request('type');

        if (! $asset) {
            throw ValidationException::withMessages(['message' => __('config.assets.nothing_uploaded', ['attribute' => $type])]);
        }

        if (\Storage::disk('public')->exists($asset)) {
            \Storage::disk('public')->delete($asset);
        }

        $this->updateAsset();
    }

    /**
     * Check symlink
     */
    public function checkSymlink() : void
    {
        if (!\File::exists(public_path('storage'))) {
            \Artisan::call('storage:link');
        }
    }
}
