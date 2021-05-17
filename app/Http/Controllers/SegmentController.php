<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\SegmentRequest;
use App\Repositories\SegmentRepository;
use App\Http\Resources\Segment as SegmentResource;

class SegmentController extends Controller
{
    protected $repo;

    /**
     * Instantiate a new instance
     * @return void
     */
    public function __construct(
        SegmentRepository $repo
    ) {
        $this->repo = $repo;
    }

    /**
     * Get segment pre requisite
     * @get ("/api/segments/pre-requisite")
     * @return array
     */
    public function preRequisite()
    {
        return $this->ok([]);
    }

    /**
     * Get all segments
     * @get ("/api/segments")
     * @return array
     */
    public function index()
    {
        return $this->repo->paginate();
    }

    /**
     * Create segment
     * @post ("/api/segments")
     * @param ({
     *      @Parameter("name", type="string", required="true", description="Segment name"),
     *      @Parameter("description", type="text", required="optional", description="Segment description"),
     * })
     * @return array
     */
    public function store(SegmentRequest $request)
    {
        $segment = $this->repo->create();

        $segment = new SegmentResource($segment);

        return $this->success(['message' => __('global.added', ['attribute' => __('contact.segment.segment')]), 'segment' => $segment]);
    }

    /**
     * Get segment detail
     * @get ("/api/segments/{uuid}")
     * @param ({
     *      @Parameter("uuid", type="uuid", required="true", description="Segment unique id"),
     * })
     * @return SegmentResource
     */
    public function show($uuid)
    {
        $segment = $this->repo->findByUuidOrFail($uuid);

        return new SegmentResource($segment);
    }

    /**
     * Update segment
     * @patch ("/api/segments/{uuid}")
     * @param ({
     *      @Parameter("uuid", type="uuid", required="true", description="Segment unique id"),
     *      @Parameter("name", type="string", required="true", description="Segment name"),
     *      @Parameter("description", type="text", required="optional", description="Segment description"),
     * })
     * @return array
     */
    public function update(SegmentRequest $request, $uuid)
    {
        $segment = $this->repo->findByUuidOrFail($uuid);

        $segment = $this->repo->update($segment);

        return $this->success(['message' => __('global.updated', ['attribute' => __('contact.segment.segment')])]);
    }

    /**
     * Delete segment
     * @delete ("/api/segments/{uuid}")
     * @param ({
     *      @Parameter("uuid", type="uuid", required="true", description="Segment unique id"),
     * })
     * @return array
     */
    public function destroy($uuid)
    {
        $segment = $this->repo->findByUuidOrFail($uuid);

        $this->repo->delete($segment);

        return $this->success(['message' => __('global.deleted', ['attribute' => __('contact.segment.segment')])]);
    }
}