<?php

namespace App\Repositories;

use App\Enums\MeetingStatus;
use App\Helpers\ArrHelper;
use App\Helpers\CalHelper;
use App\Models\Meeting;
use App\Http\Resources\MeetingCollection;
use App\Repositories\OptionRepository;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MeetingRepository
{
    protected $meeting;
    protected $option;

    /**
     * Instantiate a new instance
     * @return void
     */
    public function __construct(
        Meeting $meeting,
        OptionRepository $option
    ) {
        $this->meeting = $meeting;
        $this->option = $option;
    }

    /**
     * Find meeting with given id or throw an error
     * @param integer $id
     */
    public function findOrFail($id, $field = 'message') : Meeting
    {
        $meeting = $this->meeting->find($id);

        if (! $meeting) {
            throw ValidationException::withMessages([$field => __('global.could_not_find', ['attribute' => __('meeting.meeting')])]);
        }

        return $meeting;
    }

    /**
     * Find meeting with given uuid or throw an error
     * @param uuid $uuid
     */
    public function findByUuidOrFail($uuid, $field = 'message') : Meeting
    {
        $meeting = $this->meeting->filterByUuid($uuid)->first();

        if (! $meeting) {
            throw ValidationException::withMessages([$field => __('global.could_not_find', ['attribute' => __('meeting.meeting')])]);
        }

        return $meeting;
    }

    /**
     * Find meeting with given identifier or throw an error
     * @param string $identifier
     */
    public function findByIdentifierOrFail($identifiter, $field = 'message') : Meeting
    {
        $meeting = $this->meeting->where('meta->identifier', $identifiter)->first();

        if (! $meeting) {
            throw ValidationException::withMessages([$field => __('global.could_not_find', ['attribute' => __('meeting.meeting')])]);
        }

        return $meeting;
    }

    /**
     * Paginate all meetings
     */
    public function paginate() : MeetingCollection
    {
        $sort_by = $this->meeting->getSortBy();
        $order   = $this->meeting->getOrder();

        $keyword     = request()->query('keyword');
        $type        = request()->query('type');
        $category_uuid = request()->query('category');
        $start_date  = request()->query('start_date');
        $end_date    = request()->query('end_date');
        $status      = request()->query('status');

        $query = $this->meeting->filterByKeyword($keyword);

        if (! \Auth::user()->hasRole('admin')) {
            $query->where(function ($q) {
                $q->whereUserId(\Auth::user()->id)->orWhere(function ($q1) {
                    $contact = \Auth::user()->contact;
                    $q1->when($contact, function ($q2, $contact) {
                        $q2->whereHas('invitees', function ($q3) use ($contact) {
                            $q3->where('contact_id', $contact->id);
                        });
                    });
                });
            });
        }

        if (request()->query('instant')) {
            $query->where('meta->instant', '=', true);
        } else {
            $query->where(function($q) {
                $q->where('meta->instant', '=', null)->orWhere('meta->instant', '=', false);
            });
        }

        $query->when($type, function($q, $type) {
            $q->where('type', 'like', '%'.$type.'%');
        })->when($category_uuid, function($q, $category_uuid) {
            $q->whereHas('category', function($q1) use($category_uuid) {
                $q1->whereUuid($category_uuid);
            });
        })->when($status, function($q, $status) {
            $q->where('meta->status', $status);
        })->dateBetween([
            'start_date' => $start_date,
            'end_date'   => $end_date
        ]);

        $per_page     = request('per_page', config('config.system.per_page'));
        $current_page = request('current_page');

        return new MeetingCollection($query->orderBy($sort_by, $order)->paginate((int) $per_page, ['*'], 'current_page'));
    }

    /**
     * Get meeting pre requisite
     */
    public function getPreRequisite() : array
    {
        $categories = $this->option->listOrderedByName([ 'type' => 'meeting_category' ]);
        $statuses = ArrHelper::getTransList('statuses', 'meeting');

        $types = ArrHelper::getTransList('types', 'meeting');

        return compact('categories', 'types', 'statuses');
    }

    /**
     * Create a new meeting
     */
    public function create() : Meeting
    {
        $meeting = $this->meeting->forceCreate($this->formatParams());

        if (request('instant')) {
            $meta = $meeting->meta;
            $meta['config'] = $meta['config'] ?? [];
            $meta['accessible_via_link'] = true;
            $meeting->meta = $meta;
            $meeting->save();
            $meeting->live();
            $meeting->logAdmin();
        }

        return $meeting;
    }

    /**
     * Prepare given params for inserting into database
     * @param uuid $uuid
     */
    private function formatParams($uuid = null) : array
    {
        $category = null;

        $type = Arr::get(request('type', []), 'uuid');
        $category = $this->option->findByUuidOrFail(Arr::get(request('category', []), 'uuid'));

        if (! in_array($type, ArrHelper::getList('types', 'meeting'))) {
            throw ValidationException::withMessages(['message' => trans('global.could_not_find', ['attribute' => trans('meeting.props.type')])]);
        }

        if (request('identifier')) {
            $meeting_exists = $this->meeting->when($uuid, function($q, $uuid) {
                $q->where('uuid', '!=', $uuid);
            })->where('meta->identifier', request('identifier'))->first();

            if ($meeting_exists) {
                throw ValidationException::withMessages(['message' => trans('validation.unique', ['attribute' => trans('meeting.props.identifier')])]);
            }
        }

        $period = request('instant') ? config('config.meeting.instant_meeting_auto_ended', 60) : request('period', 60);
        $start_date_time = CalHelper::storeDateTime(request('start_date_time', now()));

        $formatted = [
            'title'           => request('title'),
            'type'            => $type ? : 'video_conference',
            'category_id'     => optional($category)->id,
            'agenda'          => request('agenda'),
            'description'     => request('description'),
            'start_date_time' => $start_date_time,
            'period'          => $period,
        ];

        if (! $uuid) {
            $formatted['meta'] = array(
                'status'              => MeetingStatus::SCHEDULED,
                'instant'             => request()->boolean('instant'),
                'accessible_via_link' => request()->boolean('accessible_via_link'),
                'is_pam'              => request()->boolean('is_pam'),
                'identifier'          => request('identifier') ? : Str::random(6),
                'keep_alive'          => request()->boolean('keep_alive'),
                'estimated_end_time'  => CalHelper::storeDateTime(Carbon::parse($start_date_time)->addMinutes($period))
            );
            $formatted['user_id'] = \Auth::user()->id;
        }

        return $formatted;
    }

    /**
     * Update given meeting
     * @param Meeting $meeting
     */
    public function update(Meeting $meeting) : Meeting
    {
        $meeting->ensureIsScheduled();

        $meeting->forceFill($this->formatParams($meeting->uuid))->save();

        $meta = $meeting->meta;
        $meta['accessible_via_link'] = request()->boolean('accessible_via_link');
        $meeting->meta = $meta;
        $meeting->save();

        return $meeting;
    }

    /**
     * Delete meeting
     * @param Meeting $meeting
     */
    public function delete(Meeting $meeting) : void
    {
        if (
            (config('config.meeting.allow_deleting_cancelled_meetings') && $meeting->getMeta('status') == MeetingStatus::CANCELLED) ||
            (config('config.meeting.allow_deleting_ended_meetings') && $meeting->getMeta('status') == MeetingStatus::ENDED)
        ) {
            $meeting->delete();
            return;
        }

        $meeting->ensureIsScheduled();

        $meeting->delete();
    }

    /**
     * Store meeting config
     *
     * @param Meeting $meeting
     */
    public function config(Meeting $meeting) : Meeting
    {
        $meeting->ensureIsScheduledOrLive();

        request()->validate([
            'enable_comments'  => 'boolean',
            'private_comments' => 'boolean',
            'enable_comment_before_meeting_starts' => 'boolean',
        ], [], [
            'enable_comments'  => __('meeting.config.enable_comments'),
            'private_comments' => __('meeting.config.private_comments'),
            'enable_comment_before_meeting_starts' => __('meeting.config.enable_comment_before_meeting_starts'),
        ]);

        return $meeting->config();
    }

    /**
     * Snooze meeting
     *
     * @param Meeting $meeting
     */
    public function snooze(Meeting $meeting) : Meeting
    {
        $meeting->ensureIsScheduled();

        request()->validate([
            'period' => 'required|integer|min:5|max:60'
        ], [], [
            'period' => __('meeting.props.period')
        ]);

        return $meeting->snooze();
    }

    /**
     * Cancel meeting
     *
     * @param Meeting $meeting
     */
    public function cancel(Meeting $meeting) : Meeting
    {
        $meeting->ensureIsScheduled();

        request()->validate([
            'cancellation_reason' => 'required'
        ], [], [
            'cancellation_reason' => __('meeting.props.cancellation_reason')
        ]);

        return $meeting->cancel();
    }
}
