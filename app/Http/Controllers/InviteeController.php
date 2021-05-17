<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConnectRequest;
use App\Http\Requests\InviteRequest;
use App\Models\Meeting;
use App\Repositories\InviteeRepository;
use App\Http\Resources\Meeting as MeetingResource;

class InviteeController extends Controller
{
    protected $repo;

    /**
     * Instantiate a new instance
     * @return void
     */
    public function __construct(
        InviteeRepository $repo
    ) {
        $this->repo = $repo;

        $this->middleware('restricted_test_mode_action')->only(['sendInvitation']);
    }

    /**
     * Get meeting invitee pre requisite
     * @get ("/api/meetings/{meeting}/invitees/pre-requisite")
     * @return array
     */
    public function preRequisite()
    {
        return $this->ok($this->repo->getPreRequisite());
    }

    /**
     * Get meeting invitees
     * @get ("/api/meeting/{meeting}/invitees")
     * @return array
     */
    public function getInvitees(Meeting $meeting)
    {
        return $this->repo->getInvitees($meeting);
    }

    /**
     * Add meeting invitees
     * @get ("/api/meeting/{meeting}/invitees")
     * @return array
     */
    public function addInvitees(Meeting $meeting)
    {
        $this->repo->addInvitees($meeting);

        return $this->success(['message' => __('global.added', ['attribute' => __('meeting.invitee.invitee')])]);
    }

    /**
     * Send meeting invitation
     * @post ("/api/meeting/{meeting}/invitation")
     * @return array
     */
    public function sendInvitation(Meeting $meeting)
    {
        $this->repo->sendInvitation($meeting);

        return $this->success(['message' => __('global.sent', ['attribute' => __('meeting.invitation')])]);
    }

    /**
     * Toggle meeting moderator
     * @post ("/api/meeting/{meeting}/moderator")
     * @return array
     */
    public function toggleModerator(Meeting $meeting)
    {
        $this->repo->toggleModerator($meeting);

        return $this->success(['message' => __('global.updated', ['attribute' => __('meeting.moderator')])]);
    }

    /**
     * Is meeting alive
     * @post ("/api/meeting/{meeting}/keep-alive")
     * @return array
     */
    public function keepAlive(Meeting $meeting)
    {
        $this->repo->keepAlive($meeting);

        return $this->success([]);
    }

    /**
     * Go to meeting
     * @get ("/m/{identifier}")
     * @param ({
     *      @Parameter("identifier", type="string", required="true", description="Meeting identifier"),
     * })
     * @redirect Meeting
     */
    public function goToMeeting($identifier)
    {
        $meeting = $this->repo->identify($identifier);

        if (\Auth::check()) {
            return redirect('/app/live/meetings/' . $meeting->uuid);
        }

        if(config('config.meeting.enable_pam') && config('config.meeting.pam_open_join_as_guest_page')) {
            return redirect('/app/guest/meetings/' . $meeting->uuid . '?identifier='. $identifier);
        }

        return redirect('/app/login-email-otp?identifier='. $identifier .'&uuid=' . $meeting->uuid .'&ref=/live/meetings/' . $meeting->uuid);
    }

    /**
     * Join meeting
     * @post ("/api/meeting/{meeting}/join")
     * @return array
     */
    public function join(Meeting $meeting)
    {
        $meeting = $this->repo->join($meeting);

        return $this->success(['message' => __('meeting.invitee_joined'), 'meeting' => new MeetingResource($meeting)]);
    }

    /**
     * Leave meeting
     * @post ("/api/meeting/{meeting}/leave")
     * @return array
     */
    public function leave(Meeting $meeting)
    {
        $meeting = $this->repo->leave($meeting);

        return $this->success(['message' => __('meeting.invitee_left'), 'meeting' => new MeetingResource($meeting)]);
    }

    /**
     * End meeting
     * @post ("/api/meeting/{meeting}/end")
     * @return array
     */
    public function end(Meeting $meeting)
    {
        $meeting->isAccessible(true);

        $meeting = $this->repo->end($meeting);

        return $this->success(['message' => __('meeting.invitee_ended'), 'meeting' => new MeetingResource($meeting)]);
    }

    /**
     * Block meeting invitee
     * @post ("/api/meeting/{meeting}/invitees/{uuid}/block")
     * @return array
     */
    public function blockInvitee(Meeting $meeting, $uuid)
    {
        $meeting->isAccessible(true);

        $this->repo->blockInvitee($meeting, $uuid);

        return $this->success(['message' => __('global.blocked', ['attribute' => __('meeting.invitee.invitee')])]);
    }

    /**
     * Unblock meeting invitee
     * @post ("/api/meeting/{meeting}/invitees/{uuid}/unblock")
     * @return array
     */
    public function unblockInvitee(Meeting $meeting, $uuid)
    {
        $meeting->isAccessible(true);

        $this->repo->unblockInvitee($meeting, $uuid);

        return $this->success(['message' => __('global.unblocked', ['attribute' => __('meeting.invitee.invitee')])]);
    }

    /**
     * Delete meeting invitee
     * @delete ("/api/meeting/{meeting}/invitees/{invitee_uuid}")
     * @return array
     */
    public function deleteInvitee(Meeting $meeting, $invitee_uuid)
    {
        $this->repo->deleteInvitee($meeting, $invitee_uuid);

        return $this->success(['message' => __('global.deleted', ['attribute' => __('meeting.invitee.invitee')])]);
    }

    /**
     * Alert meeting
     * @post ("/api/meetings/{uuid}/alert")
     * @param ({
     *      @Parameter("uuid", type="uuid", required="true", description="Meeting unique id"),
     * })
     * @return array
     */
    public function alert(Meeting $meeting)
    {
        $this->authorize('list', Meeting::class);

        $meeting->isAccessible(true);

        $this->repo->alert($meeting);

        return $this->success(['message' => 'done']);
    }
}
