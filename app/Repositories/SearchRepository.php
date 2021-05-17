<?php

namespace App\Repositories;

use App\Http\Resources\ContactCollection;
use App\Http\Resources\MeetingCollection;
use App\Http\Resources\UserCollection;
use App\Models\Contact;
use App\Models\Meeting;
use App\Models\User;

class SearchRepository
{
    protected $meeting;
    protected $contact;
    protected $user;

    /**
     * Instantiate a new instance
     * @return void
     */
    public function __construct(
        Meeting $meeting,
        Contact $contact,
        User $user
    ) {
        $this->meeting = $meeting;
        $this->contact = $contact;
        $this->user = $user;
    }

    /**
     * Search term
     */
    public function search() : array
    {
        if (strlen(request()->query('q')) < 3) {
            return [];
        }

        $term = request()->query('q');

        $per_page = request('per_page', config('config.system.per_page'));

        $meetings = new MeetingCollection($this->meeting->where('title', 'like', '%'.$term.'%')->orderBy('start_date_time', 'desc')->orderBy('created_at', 'desc')->orderBy('title', 'asc')->take($per_page)->get());

        $contacts = \Auth::user()->can('access-contact') ? new ContactCollection($this->contact->visibility()->filterByKeyword($term)->orderBy('created_at', 'desc')->orderBy('name', 'asc')->take($per_page)->get()) : [];

        $users = \Auth::user()->can('list-user') ? new UserCollection($this->user->filterByKeyword($term)->orderBy('created_at', 'desc')->orderBy('name', 'asc')->take($per_page)->get()) : [];

        return compact('meetings', 'contacts', 'users');
    }
}
