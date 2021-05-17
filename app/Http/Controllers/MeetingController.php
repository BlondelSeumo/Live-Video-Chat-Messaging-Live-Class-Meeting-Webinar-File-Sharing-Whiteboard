<?php

namespace App\Http\Controllers;

use App\Enums\MeetingStatus;
use App\Models\Meeting;
use App\Http\Requests\MeetingRequest;
use App\Http\Resources\Meeting as MeetingResource;
use App\Repositories\MeetingRepository;

class MeetingController extends Controller
{
    protected $repo;

    /**
     * Instantiate a new controller instance
     * @return void
     */
    public function __construct(
        MeetingRepository $repo
    ) {
        $this->repo = $repo;
    }

    /**
     * Get meeting pre requisite
     * @get ("/api/meetings/pre-requisite")
     * @return array
     */
    public function preRequisite()
    {
        $this->authorize('preRequisite', Meeting::class);

        return $this->ok($this->repo->getPreRequisite());
    }

    /**
     * Get all meetings
     * @get ("/api/meetings")
     * @return array
     */
    public function index()
    {
        $this->authorize('list', Meeting::class);

        return $this->repo->paginate();
    }

    /**
     * Store meeting
     * @post ("/api/meetings")
     * @param ({
     *      @Parameter("title", type="string", required="true", description="Meeting title"),
     *      @Parameter("agenda", type="text", required="true", description="Meeting agenda"),
     *      @Parameter("description", type="text", required="true", description="Meeting description"),
     *      @Parameter("start_date_time", type="datetime", required="true", description="Meeting start date time"),
     *      @Parameter("period", type="integer", required="true", description="Meeting estimated period (in minutes)"),
     * })
     * @return array
     */
    public function store(MeetingRequest $request)
    {
        $this->authorize('create', Meeting::class);

        $meeting = $this->repo->create();

        $meeting = new MeetingResource($meeting);

        return $this->success(['message' => __('global.added', ['attribute' => __('meeting.meeting')]), 'meeting' => $meeting]);
    }

    /**
     * Get meeting detail
     * @get ("/api/meetings/{uuid}")
     * @param ({
     *      @Parameter("uuid", type="uuid", required="true", description="Meeting unique id"),
     * })
     * @return MeetingResource
     */
    public function show(Meeting $meeting)
    {
        $this->authorize('show', Meeting::class);

        $meeting->isAccessible();

        $meeting->isCancellable();

        return new MeetingResource($meeting);
    }

    /**
     * Get pam meeting detail
     * @get ("/api/meetings/{uuid}/pam")
     * @param ({
     *      @Parameter("uuid", type="uuid", required="true", description="Meeting unique id"),
     * })
     * @return MeetingResource
     */
    public function pam(Meeting $meeting)
    {
        if (! $meeting->getMeta('is_pam')) {
            return $this->error(['message' => __('general.invalid_action')]);
        }

        if ($meeting->getMeta('status') === MeetingStatus::ENDED) {
            return $this->error(['message' => __('meeting.meeting_ended')]);
        }

        return new MeetingResource($meeting);
    }

    /**
     * Get meeting detail from identifier
     * @get ("/api/meetings/pam/{identifier}")
     * @param ({
     *      @Parameter("identifier", type="string", required="true", description="Meeting identifier"),
     * })
     * @return MeetingResource
     */
    public function showPam($identifier)
    {
        $meeting = $this->repo->findByIdentifierOrFail($identifier);

        if (! $meeting->getMeta('is_pam')) {
            return $this->error(['message' => __('general.invalid_action')]);
        }

        if ($meeting->getMeta('status') === MeetingStatus::ENDED) {
            return $this->error(['message' => __('meeting.meeting_ended')]);
        }

        return new MeetingResource($meeting);
    }

    /**
     * Get meeting detail from identifier
     * @get ("/api/meetings/m/{identifier}")
     * @param ({
     *      @Parameter("identifier", type="string", required="true", description="Meeting identifier"),
     * })
     * @return MeetingResource
     */
    public function showMeeting($identifier)
    {
        $this->authorize('show', Meeting::class);

        $meeting = $this->repo->findByIdentifierOrFail($identifier);

        $meeting->isAccessible();

        $meeting->isCancellable();

        return new MeetingResource($meeting);
    }

    /**
     * Update meeting
     * @patch ("/api/meetings/{uuid}")
     * @param ({
     *      @Parameter("uuid", type="uuid", required="true", description="Meeting unique id"),
     *      @Parameter("title", type="string", required="true", description="Meeting title"),
     *      @Parameter("agenda", type="text", required="true", description="Meeting agenda"),
     *      @Parameter("description", type="text", required="true", description="Meeting description"),
     *      @Parameter("start_date_time", type="datetime", required="true", description="Meeting start date time"),
     *      @Parameter("period", type="integer", required="true", description="Meeting estimated period (in minutes)"),
     * })
     * @return array
     */
    public function update(MeetingRequest $request, Meeting $meeting)
    {
        $this->authorize('update', Meeting::class);

        $meeting->isAccessible(true);

        $meeting = $this->repo->update($meeting);

        return $this->success(['message' => __('global.updated', ['attribute' => __('meeting.meeting')])]);
    }

    /**
     * Delete meeting
     * @delete ("/api/meetings/{uuid}")
     * @param ({
     *      @Parameter("uuid", type="uuid", required="true", description="Meeting unique id"),
     * })
     * @return array
     */
    public function destroy(Meeting $meeting)
    {
        $this->authorize('delete', Meeting::class);

        $meeting->isAccessible(true);

        $this->repo->delete($meeting);

        return $this->success(['message' => __('global.deleted', ['attribute' => __('meeting.meeting')])]);
    }

    /**
     * Store meeting configuration
     * @post ("/api/meetings/{uuid}/config")
     * @param ({
     *      @Parameter("uuid", type="uuid", required="true", description="Meeting unique id"),
     * })
     * @return array
     */
    public function config(Meeting $meeting)
    {
        $this->authorize('list', Meeting::class);

        $meeting->isAccessible(true);

        $meeting = $this->repo->config($meeting);

        return $this->success(['message' => __('global.updated', ['attribute' => __('meeting.meeting')]), 'meeting' => new MeetingResource($meeting)]);
    }

    /**
     * Snooze meeting
     * @post ("/api/meetings/{uuid}/snooze")
     * @param ({
     *      @Parameter("uuid", type="uuid", required="true", description="Meeting unique id"),
     * })
     * @return array
     */
    public function snooze(Meeting $meeting)
    {
        $this->authorize('list', Meeting::class);

        $meeting->isAccessible(true);

        $meeting = $this->repo->snooze($meeting);

        return $this->success(['message' => __('global.snoozed', ['attribute' => __('meeting.meeting')]), 'meeting' => new MeetingResource($meeting)]);
    }

    /**
     * Cancel meeting
     * @post ("/api/meetings/{uuid}/cancel")
     * @param ({
     *      @Parameter("uuid", type="uuid", required="true", description="Meeting unique id"),
     * })
     * @return array
     */
    public function cancel(Meeting $meeting)
    {
        $this->authorize('list', Meeting::class);

        $meeting->isAccessible(true);

        $meeting = $this->repo->cancel($meeting);

        return $this->success(['message' => __('global.cancelled', ['attribute' => __('meeting.meeting')]), 'meeting' => new MeetingResource($meeting)]);
    }
}
