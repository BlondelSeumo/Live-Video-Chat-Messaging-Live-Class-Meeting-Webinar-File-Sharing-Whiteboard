<?php

namespace App\Repositories;

use App\Enums\MeetingStatus;
use App\Helpers\CalHelper;
use App\Http\Resources\InviteeCollection;
use App\Models\Invitee;
use App\Models\Meeting;
use App\Models\User;
use App\Notifications\MeetingInvitation;
use App\Repositories\ContactRepository;
use App\Repositories\MeetingRepository;
use App\Repositories\SegmentRepository;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;

class InviteeRepository
{
    protected $meeting;
    protected $invitee;
    protected $segment;
    protected $contact;

    /**
     * Instantiate a new instance
     * @return void
     */
    public function __construct(
        MeetingRepository $meeting,
        Invitee $invitee,
        SegmentRepository $segment,
        ContactRepository $contact
    ) {
        $this->meeting = $meeting;
        $this->invitee = $invitee;
        $this->segment = $segment;
        $this->contact = $contact;
    }

    /**
     * Get pre requisites
     */
    public function getPreRequisite() : array
    {
        $segments = $this->segment->getAll();
        $contacts = $this->contact->getAll();

        return compact('segments', 'contacts');
    }

    /**
     * Get meeting invitee
     *
     * @param Meeting $meeting
     */
    public function getInvitees(Meeting $meeting) : InviteeCollection
    {
        $meeting->isAccessible();

        $sort_by = request()->query('sort_by', 'created_at');
        $order   = request()->query('order', 'desc');

        $per_page     = request('per_page', config('config.system.per_page'));
        $current_page = request('current_page');

        return new InviteeCollection($this->invitee->whereMeetingId($meeting->id)->orderBy($sort_by, $order)->paginate((int) $per_page, ['*'], 'current_page'));
    }

    /**
     * Add meeting invitee
     *
     * @param Meeting $meeting
     */
    public function addInvitees(Meeting $meeting) : void
    {
        if (! $meeting->getMeta('room') && ! $meeting->getMeta('instant')) {
            $meeting->ensureIsScheduled();
        }

        $meeting->isAccessible(true);

        $contact_uuids = collect(request('contacts', []))->pluck('uuid')->all();
        $segment_uuids = collect(request('segments', []))->pluck('uuid')->all();

        $contacts = $this->contact->filterByUuids($contact_uuids);
        $segments = $this->segment->filterByUuids($segment_uuids);

        $invitees = $contacts->pluck('id')->all();
        foreach ($segments as $segment) {
            $invitees = array_merge($invitees, $segment->contacts()->pluck('id')->all());
        }

        $invitees = array_merge($invitees, $this->contact->addEmailInvitees(request('emails')));
        $invitees = array_unique($invitees);

        foreach ($invitees as $invitee) {
            $this->invitee->firstOrCreate([
                'meeting_id' => $meeting->id,
                'contact_id' => $invitee
            ]);
        }
    }

    /**
     * Send meeting invitation
     *
     * @param Meeting $meeting
     */
    public function sendInvitation(Meeting $meeting) : void
    {
        $meeting->ensureIsScheduled();

        $meeting->isAccessible(true);

        $invitee_uuid = Arr::get(request('invitee', []), 'uuid');
        $invitees = $this->invitee->whereMeetingId($meeting->id)
            ->when($invitee_uuid, function ($q, $invitee_uuid) {
                return $q->whereUuid($invitee_uuid);
            })->get();

        foreach ($invitees as $invitee) {
            (new User)->forceFill([
                'email' => $invitee->contact->email,
            ])->notify(new MeetingInvitation($meeting, $invitee->contact));
        }
    }

    /**
     * Toggle meeting moderator
     *
     * @param Meeting $meeting
     */
    public function toggleModerator(Meeting $meeting) : void
    {
        $meeting->ensureIsScheduled();

        $invitee_uuid = Arr::get(request('invitee', []), 'uuid');
        $invitee = $this->invitee->whereMeetingId($meeting->id)->whereUuid($invitee_uuid)->first();

        if (! $invitee) {
            throw ValidationException::withMessages(['message' => __('global.could_not_find', ['attribute' => __('meeting.invitee.invtee')])]);
        }

        $meta = $invitee->meta;
        $meta['is_moderator'] = ! $invitee->getMeta('is_moderator');
        $invitee->meta = $meta;
        $invitee->save();
    }

    /**
     * Check if meeting is alive
     *
     * @param Meeting $meeting
     */
    public function keepAlive(Meeting $meeting) : void
    {
        if ($meeting->getMeta('status') != MeetingStatus::LIVE) {
            return;
        }

        $estimated_end_time = Carbon::parse($meeting->getMeta('estimated_end_time'));

        if (now()->addMinutes(config('config.meeting.end_time_increase_period')) < $estimated_end_time) {
            $meta = $meeting->meta;
            $meta['estimated_end_time'] = CalHelper::storeDateTime($estimated_end_time->addMinutes(config('config.meeting.end_time_increase_period')));
            $meeting->meta = $meta;
            $meeting->save();
        }
    }

    /**
     * Identify meeting
     *
     * @param string $identifer
     */
    public function identify($identifer)
    {
        $meeting = Meeting::with('invitees')->where('meta->identifier', $identifer)->firstOrFail();

        if (\Auth::check() && $meeting->getMeta('accessible_via_link')) {
            $this->addYourselfAsInvitee($meeting);
        }

        return $meeting;
    }

    /**
     * Add yourself as Invitee
     *
     * @param Meeting $meeting
     */
    private function addYourselfAsInvitee(Meeting $meeting) : void
    {
        if (! \Auth::user()->contact) {
            return;
        }

        if (\Auth::user()->id === $meeting->user_id) {
            return;
        }

        if ($this->invitee->whereMeetingId($meeting->id)->whereContactId(\Auth::user()->contact->id)->count()) {
            return;
        }

        $this->invitee->firstOrCreate([
            'meeting_id' => $meeting->id,
            'contact_id' => \Auth::user()->contact->id
        ]);
    }

    /**
     * Join meeting
     *
     * @param Meeting $meeting
     */
    public function join(Meeting $meeting) : Meeting
    {
        $meeting->ensureIsNotEnded();

        if ($meeting->getMeta('is_pam') && ! \Auth::check()) {
            $meeting->ensureIsLive();
            return $meeting;
        }

        if ($meeting->user_id === \Auth::user()->id) {
            $meeting->live();

            return $meeting->logAdmin();
        }

        if ($meeting->getMeta('accessible_via_link')) {
            $this->addYourselfAsInvitee($meeting);
        }

        $invitee = $meeting->getInvitee();

        if ($invitee->getMeta('is_blocked')) {
            throw ValidationException::withMessages(['message' => trans('user.permission_denied')]);
        }

        if ($invitee->getMeta('is_moderator')) {
            $meeting->live();
        } else {
            $meeting->ensureIsLive();
        }

        $invitee->logInvitee();

        return $meeting;
    }

    /**
     * Join meeting from OTP
     * @param uuid $meeting_uuid
     */
    public function joinWithOtp($meeting_uuid) : void
    {
        $meeting = Meeting::whereUuid($meeting_uuid)->first();

        if (! $meeting) {
            return;
        }

        $meeting->ensureIsNotEnded();

        if ($meeting->getMeta('accessible_via_link')) {
            $this->addYourselfAsInvitee($meeting);
        }
    }

    /**
     * Leave meeting
     *
     * @param Meeting $meeting
     */
    public function leave(Meeting $meeting) : Meeting
    {
        $meeting->ensureIsLive();

        if ($meeting->getMeta('is_pam') && ! \Auth::check()) {
            return $meeting;
        }

        if ($meeting->user_id === \Auth::user()->id) {
            return $meeting;
        }

        $invitee = $meeting->getInvitee();

        $invitee->logError();

        return $meeting;
    }

    /**
     * End meeting
     *
     * @param Meeting $meeting
     */
    public function end(Meeting $meeting) : Meeting
    {
        $meeting->ensureIsLive();

        if ($meeting->user_id === \Auth::user()->id) {
            return $meeting->end();
        }

        $invitee = $meeting->getInvitee();

        if ($invitee->getMeta('is_moderator')) {
            return $meeting->end();
        }

        return $meeting;
    }

    /**
     * Get invitee from meeting & uuid
     *
     * @param int $meeting_id
     * @param uuid $uuid
     */
    private function getInviteeFromUuid($meeting_id, $uuid) : Invitee
    {
        $uuid_type = request('type') == 'invitee' ? request('type') : 'user';

        $query = $this->invitee->whereMeetingId($meeting_id);

        if ($uuid_type == 'user') {
            $query->whereHas('contact', function($q1) use($uuid) {
                $q1->whereHas('user', function($q2) use($uuid) {
                    $q2->where('uuid', $uuid);
                });
            });
        } else {
            $query->where('uuid', $uuid);
        }

        $invitee = $query->first();

        if (! $invitee) {
            throw ValidationException::withMessages(['message' => __('global.could_not_find', ['attributes' => __('meeting.invitee.invitee')])]);
        }

        return $invitee;
    }

    /**
     * Block meeting invitee
     *
     * @param Meeting $meeting
     * @param uuid $uuid
     */
    public function blockInvitee(Meeting $meeting, $uuid) : void
    {
        $meeting->ensureIsScheduledOrLive();

        $invitee = $this->getInviteeFromUuid($meeting->id, $uuid);

        $meta = $invitee->meta;
        $meta['is_blocked'] = true;
        $invitee->meta = $meta;
        $invitee->save();
    }

    /**
     * Unblock meeting invitee
     *
     * @param Meeting $meeting
     * @param uuid $uuid
     */
    public function unblockInvitee(Meeting $meeting, $uuid) : void
    {
        $meeting->ensureIsScheduledOrLive();

        $invitee = $this->getInviteeFromUuid($meeting->id, $uuid);

        $meta = $invitee->meta;
        unset($meta['is_blocked']);
        $invitee->meta = $meta;
        $invitee->save();
    }

    /**
     * Delete meeting invitee
     *
     * @param Meeting $meeting
     * @param uuid $invitee_uuid
     */
    public function deleteInvitee(Meeting $meeting, $invitee_uuid) : void
    {
        $meeting->ensureIsScheduled();

        $invitee = $this->invitee->whereMeetingId($meeting->id)->whereUuid($invitee_uuid)->first();

        if (! $invitee) {
            throw ValidationException::withMessages(['message' => __('global.could_not_find', ['attributes' => __('meeting.invitee')])]);
        }

        $invitee->delete();
    }

    /**
     * Alert meeting
     *
     * @param Meeting $meeting
     */
    public function alert(Meeting $meeting) : Void
    {
        $meeting->ensureIsLive();

        if ($meeting->user_id === \Auth::user()->id) {
            $meeting->alert();
        }

        $invitee = $meeting->getInvitee();

        if ($invitee->getMeta('is_moderator')) {
            $meeting->alert();
        }
    }
}
