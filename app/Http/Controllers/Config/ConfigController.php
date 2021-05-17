<?php

namespace App\Http\Controllers\Config;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Notifications\DemoNotification;
use App\Repositories\Config\ConfigRepository;

class ConfigController extends Controller
{
    protected $repo;
    protected $request;

    /**
     * Instantiate a new controller instance
     * @return void
     */
    public function __construct(
        ConfigRepository $repo,
        Request $request
    ) {
        $this->repo = $repo;
        $this->request = $request;

        $this->middleware('restricted_test_mode_action')->only(['store']);
    }

    /**
     * Get config pre requisite
     * @get ("/api/config/pre-requisite")
     * @return array
     */
    public function preRequisite()
    {
        return $this->ok($this->repo->getPreRequisite());
    }

    /**
     * Get config
     * @get ("/api/config")
     * @return array
     */
    public function index()
    {
        return $this->ok($this->repo->getConfig());
    }

    /**
     * Store config
     * @post ("/api/config")
     * @param ({
     *      @Parameter("config", type="array", required="optional", description="Config variables"),
     * })
     * @return array
     */
    public function store()
    {
        $this->repo->store();

        return $this->success(['message' => __('global.stored', ['attribute' => __('config.config')])]);
    }

    /**
     * Store notification vapid key
     * @post ("/api/config/notification")
     * @return array
     */
    public function notification()
    {
        $this->repo->setNotificationConfig();

        return $this->success(['message' => __('global.stored', ['attribute' => __('config.config')])]);
    }

    /**
     * Show demo notification
     * @get ("/api/config/notification")
     * @return array
     */
    public function showDemoNotification(Request $request)
    {
        $request->user()->notify(new DemoNotification);
    }

    /**
     * Upload asset
     * @post ("/api/config/assets")
     * @param ({
     *      @Parameter("asset", type="file", required="true", description="Asset to be uploaded"),
     * })
     * @return array
     */
    public function uploadAsset()
    {
        $type = request('type');

        $config = $this->repo->uploadAsset();

        return $this->success(['message' => __('global.uploaded', ['attribute' => __('config.assets.'.$type)]), 'asset' => $config->getValue($type)]);
    }

    /**
     * Remove asset
     * @post ("/api/config/assets")
     * @return Response
     */
    public function removeAsset()
    {
        $type = request('type');
        
        $this->repo->removeAsset();

        return $this->success(['message' => __('global.removed', ['attribute' => __('config.assets.'.$type)])]);
    }
}
