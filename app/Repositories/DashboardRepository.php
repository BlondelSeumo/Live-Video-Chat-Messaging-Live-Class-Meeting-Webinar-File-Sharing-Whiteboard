<?php

namespace App\Repositories;

use App\Helpers\ArrHelper;
use App\Helpers\CalHelper;
use App\Http\Resources\MeetingCollection;
use App\Models\Meeting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class DashboardRepository
{
    protected $user;
    protected $meeting;

    public function __construct(
        User $user,
        Meeting $meeting
    ) {
        $this->user = $user;
        $this->meeting = $meeting;
    }

    /**
     * Get meetings for dashbaord
     */
    public function getMeetings() : array
    {
        $meetings = $this->meeting->myMeeting()->where('meta->instant', '=', null)->where('start_date_time', '>', now())->isScheduled()->orderBy('start_date_time', 'asc')->take(5)->get();

        $recent_meetings = $this->meeting->myMeeting()->where('meta->instant', '=', null)->where('meta->ended_at', '<', now())->isNotCancelled()->orderBy('meta->ended_at', 'desc')->take(5)->get();

        $recent_meetings->each(function ($meeting) use ($meetings) {
            $meetings->push($meeting);
        });

        $meetings = new MeetingCollection($meetings);

        return compact('meetings');
    }

    /**
     * Get stats for dashboard
     */
    public function getStats() : array
    {
        $stats = array();
        array_push($stats, $this->getUpcomingMeetings());
        array_push($stats, $this->getHostedMeetings());
        array_push($stats, $this->getAttendedMeetings());
        array_push($stats, $this->getInstantMeetings());

        return $stats;
    }

    /**
     * Get user count for dashboard
     */
    private function getUserStats() : array
    {
        $users = $this->user->get();
        $today = $users->filter(function ($q) {
            return CalHelper::toDate($q->created_at) === CalHelper::today();
        })->count();

        return array(
            'value' => $users->count(),
            'label' => trans('dashboard.stats.total', ['attribute' => trans('user.users')]),
            'color' => 'bg-gray-darker',
            'icon' => 'fas fa-users',
            'extra' => array(
                'today' => array(
                    'value' => $today,
                    'label' => trans('dashboard.stats.today', ['attribute' => trans('user.users')]),
                    'color' => $today ? 'text-success' : 'text-dark',
                    'icon' => 'fas fa-arrow-up'
                )
            )
        );
    }

    /**
     * Get upcoming meeting count for dashboard
     */
    private function getUpcomingMeetings() : array
    {
        $meetings = $this->meeting->myMeeting()->isScheduled()->where('start_date_time', '>', now())->get();
        $today = $meetings->filter(function ($q) {
            return CalHelper::toDate($q->start_date_time) === CalHelper::today();
        })->count();

        return array(
            'value' => $meetings->count(),
            'label' => trans('meeting.upcoming_meetings'),
            'color' => 'bg-primary',
            'icon' => 'far fa-calendar-alt',
            'extra' => array(
                'today' => array(
                    'value' => $today,
                    'label' => trans('dashboard.stats.today', ['attribute' => trans('meeting.upcoming_meetings')]),
                    'color' => $today ? 'text-success' : 'text-dark',
                    'icon' => 'fas fa-arrow-up'
                )
            )
        );
    }

    /**
     * Get instant meeting count for dashboard
     */
    private function getInstantMeetings() : array
    {
        $meetings = $this->meeting->isNotCancelled()->whereUserId(\Auth::user()->id)->where('meta->instant', '=', true)->get();

        $today = $meetings->filter(function ($q) {
            return CalHelper::toDate($q->start_date_time) === CalHelper::today();
        })->count();

        return array(
            'value' => $meetings->count(),
            'label' => trans('meeting.instant_meetings'),
            'color' => 'bg-warning',
            'icon' => 'fas fa-business-time',
            'extra' => array(
                'today' => array(
                    'value' => $today,
                    'label' => trans('dashboard.stats.today', ['attribute' => trans('meeting.instant_meetings')]),
                    'color' => $today ? 'text-success' : 'text-dark',
                    'icon' => 'fas fa-arrow-up'
                )
            )
        );
    }
    
    /**
     * Get hosted meeting count for dashboard
     */
    private function getHostedMeetings() : array
    {
        $meetings = $this->meeting->isNotCancelled()->whereUserId(\Auth::user()->id)->get();
        $today = $meetings->filter(function ($q) {
            return CalHelper::toDate($q->start_date_time) === CalHelper::today();
        })->count();

        return array(
            'value' => $meetings->count(),
            'label' => trans('meeting.hosted_meetings'),
            'color' => 'bg-info',
            'icon' => 'far fa-user-circle',
            'extra' => array(
                'today' => array(
                    'value' => $today,
                    'label' => trans('dashboard.stats.today', ['attribute' => trans('meeting.hosted_meetings')]),
                    'color' => $today ? 'text-success' : 'text-dark',
                    'icon' => 'fas fa-arrow-up'
                )
            )
        );
    }

    /**
     * Get attended meeting count for dashboard
     */
    private function getAttendedMeetings() : array
    {
        $meetings = $this->meeting->isNotCancelled()->attendedMeeting()->get();
        $today = $meetings->filter(function ($q) {
            return CalHelper::toDate($q->start_date_time) === CalHelper::today();
        })->count();

        return array(
            'value' => $meetings->count(),
            'label' => trans('meeting.attended_meetings'),
            'color' => 'bg-success',
            'icon' => 'far fa-calendar-check',
            'extra' => array(
                'today' => array(
                    'value' => $today,
                    'label' => trans('dashboard.stats.today', ['attribute' => trans('meeting.attended_meetings')]),
                    'color' => $today ? 'text-success' : 'text-dark',
                    'icon' => 'fas fa-arrow-up'
                )
            )
        );
    }

    /**
     * Get chart for dashboard
     */
    public function getCharts()
    {
        $meetings = $this->meeting->myMeeting()
            ->isNotCancelled()
            ->where('start_date_time', '>=', Carbon::parse(now())->startOfYear()->toDateTimeString())
            ->where('start_date_time', '<=', Carbon::parse(now())->endOfYear()->toDateTimeString())
            ->get();

        $dataset = array();
        $labels = array();
        foreach (ArrHelper::getTransList('months', 'general', false) as $month) {
            $labels[] = Arr::get($month, 'name');

            $date = '1-'. Arr::get($month, 'uuid').'-'.date('Y');
            
            $dataset[] = $meetings->filter(function ($meeting) use ($date) {
                    return $meeting->start_date_time >= Carbon::parse($date)->toDateTimeString() &&
                    $meeting->start_date_time <= Carbon::parse($date)->endOfMonth()->endOfDay()->toDateTimeString();
            })->count();
        }

        $datasets[] = array(
            'label' => trans('meeting.meetings'),
            'backgroundColor' => 'rgba(88,27,152,0.5)',
            'borderColor' => '#581b98',
            'borderWidth' => 1,
            'data' => $dataset,
        );


        return array(
            'type' => 'bar',
            'title' => trans('dashboard.chart'),
            'chart_data' => array(
                'labels' => $labels,
                'datasets' => $datasets
            ),
            'options' => array()
        );
    }
}
