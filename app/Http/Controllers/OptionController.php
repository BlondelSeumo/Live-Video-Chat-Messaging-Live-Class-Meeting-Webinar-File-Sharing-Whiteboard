<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\OptionRequest;
use App\Http\Resources\Option as OptionResource;
use App\Models\Option;
use App\Repositories\OptionRepository;
use Illuminate\Http\Request;

class OptionController extends Controller
{
    private $request;
    private $repo;

    /**
     * Instantiate a new controller instance
     * @return void
     */
    public function __construct(
        Request $request,
        OptionRepository $repo
    ) {
        $this->request  = $request;
        $this->repo     = $repo;

        $this->middleware('permission:access-config');
    }

    /**
     * Get pre requisites
     * @get ("/api/options/pre-requisite")
     * @return array
     */
    public function preRequisite()
    {
        return $this->ok($this->repo->getPreRequisite());
    }

    /**
     * Get all options
     * @get ("/api/options")
     * @return array
     */
    public function index()
    {
        return $this->repo->paginate();
    }

    /**
     * Store option
     * @post ("/api/options")
     * @param ({
     *      @Parameter("title", type="string", required="true", description="Option title"),
     *      @Parameter("description", type="text", required="optional", description="Option description"),
     *      @Parameter("code", type="string", required="true", description="Option code"),
     * })
     * @return array
     */
    public function store(OptionRequest $request)
    {
        $option = new OptionResource($this->repo->create());

        return $this->success(['message' => __('global.added', ['attribute' => __('option.option')]), 'option' => $option]);
    }

    /**
     * Get option detail
     * @get ("/api/options/{uuid}")
     * @param ({
     *      @Parameter("uuid", type="uuid", required="true", description="Option unique id"),
     * })
     * @return OptionResource
     */
    public function show($uuid)
    {
        return new OptionResource($this->repo->findByUuidOrFail($uuid));
    }

    /**
     * Update option
     * @patch ("/api/options/{uuid}")
     * @param ({
     *      @Parameter("uuid", type="uuid", required="true", description="Option unique id"),
     *      @Parameter("title", type="string", required="true", description="Option title"),
     *      @Parameter("description", type="text", required="optional", description="Option description"),
     *      @Parameter("code", type="string", required="true", description="Option code"),
     * })
     * @return array
     */
    public function update($uuid, OptionRequest $request)
    {
        $this->repo->update($this->repo->findByUuidOrFail($uuid));

        return $this->success(['message' => __('global.updated', ['attribute' => __('option.option')])]);
    }

    /**
     * Delete option
     * @delete ("/api/options/{uuid}")
     * @param ({
     *      @Parameter("uuid", type="uuid", required="true", description="Option unique id"),
     * })
     * @return array
     */
    public function destroy($uuid)
    {
        $this->repo->delete($this->repo->findByUuidOrFail($uuid));

        return $this->success(['message' => __('global.deleted', ['attribute' => __('option.option')])]);
    }
}
