<?php

namespace App\Repositories;

use App\Models\Contact;
use App\Http\Resources\ContactCollection;
use App\Repositories\Auth\UserRepository;
use App\Repositories\SegmentRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class ContactRepository
{
    protected $contact;
    protected $segment;
    protected $user;

    /**
     * Instantiate a new instance
     * @return void
     */
    public function __construct(
        Contact $contact,
        SegmentRepository $segment,
        UserRepository $user
    ) {
        $this->contact = $contact;
        $this->segment = $segment;
        $this->user = $user;
    }

    /**
     * Get all segments
     */
    public function getAll() : ContactCollection
    {
        return new ContactCollection($this->contact->visibility()->get());
    }

    /**
     * Find contact with given id or throw an error
     * @param integer $id
     */
    public function findOrFail($id, $field = 'message') : Contact
    {
        $contact = $this->contact->visibility()->whereId($id)->first();

        if (! $contact) {
            throw ValidationException::withMessages([$field => __('global.could_not_find', ['attribute' => __('contact.contact')])]);
        }

        return $contact;
    }

    /**
     * Find contact with given uuid or throw an error
     * @param uuid $uuid
     */
    public function findByUuidOrFail($uuid, $field = 'message') : Contact
    {
        $contact = $this->contact->visibility()->whereUuid($uuid)->first();

        if (! $contact) {
            throw ValidationException::withMessages([$field => __('global.could_not_find', ['attribute' => __('contact.contact')])]);
        }

        return $contact;
    }

    /**
     * Filter by uuids
     *
     * @param array $uuids
     */
    public function filterByUuids($uuids = array()) : Collection
    {
        return $this->contact->visibility()->whereIn('uuid', $uuids)->get();
    }

    /**
     * Paginate all contacts
     */
    public function paginate() : ContactCollection
    {
        $sort_by = request()->query('sort_by', 'created_at');
        $order   = request()->query('order', 'desc');
        $name    = request()->query('name');
        $email   = request()->query('email');

        $query = $this->contact->filterByName($name)->filterByEmail($email)->visibility();

        $per_page     = request('per_page', config('config.system.per_page'));
        $current_page = request('current_page');

        return new ContactCollection($query->orderBy($sort_by, $order)->paginate((int) $per_page, ['*'], 'current_page'));
    }

    /**
     * Get contact pre requisite
     */
    public function getPreRequisite() : array
    {
        $segments = $this->segment->getAll();

        return compact('segments');
    }

    /**
     * Validate input
     */
    private function validateInput() : void
    {
        $existing_contact_query = $this->contact->whereEmail(request('email'))->whereHas('users', function($q) {
            $q->where('user_id', \Auth::id());
        });

        if ($existing_contact_query->count()) {
            throw ValidationException::withMessages(['email' => trans('validation.unique', ['attribute' => trans('contact.contact')])]);
        }
    }

    /**
     * Create a new contact
     */
    public function create() : Contact
    {
        $this->validateInput();

        \DB::beginTransaction();

        $contact = $this->contact->firstOrCreate(['email' => request('email')]);

        $contact = $this->updateUser($contact);
        $this->syncSegment($contact);

        $contact->users()->attach(\Auth::id(), ['name' => request('name')]);

        \DB::commit();

        return $contact;
    }

    /**
     * Update user contact
     *
     * @param Contact $contact
     * @return Contact
     */
    private function updateUser(Contact $contact) : Contact
    {
        $user = $this->user->findByEmail($contact->email);
        
        if ($user) {
            $contact->user_id = $user->id;
            $contact->save();
        }

        return $contact;
    }

    /**
     * Sync contact segment
     *
     * @param Contact $contact
     */
    private function syncSegment(Contact $contact) : void
    {
        $segments = collect(request('segments', []));

        $segments = $this->segment->findIdsByUuids($segments->pluck('uuid')->all());

        $contact->segments()->sync($segments);
    }
    
    /**
     * Add email as contact
     *
     * @param array $emails
     * @return array
     */
    public function addEmailInvitees($emails) : array
    {
        $valid_emails = array();

        foreach ($emails as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $valid_emails[] = $email;
            }
        }

        $contact_ids = array();
        foreach ($valid_emails as $email) {
            $contact = $this->contact->firstOrCreate(['email' => $email]);
            $contact->users()->syncWithoutDetaching([\Auth::id()]);

            $contact_ids[] = $contact->id;
        }

        return $contact_ids;
    }

    /**
     * Update given contact
     * @param Contact $contact
     */
    public function update(Contact $contact) : Contact
    {
        \DB::beginTransaction();

        if ($contact->email == request('email')) {
            $contact->users()->syncWithoutDetaching([\Auth::id() => ['name' => request('name')]]);

            $contact = $this->updateUser($contact);
            $this->syncSegment($contact);

            \DB::commit();

            return $contact;
        }

        $this->validateInput();

        if ($contact->users()->where('user_id', '!=', \Auth::id())->pluck('user_id')->all()) {
            $new_contact = $this->contact->firstOrCreate(['email' => request('email')]);

            $new_contact->users()->sync([\Auth::id() => ['name' => request('name')]]);
            $contact->users()->detach([\Auth::id()]);

            $contact = $this->updateUser($contact);
            $this->syncSegment($contact);

            \DB::commit();

            return $contact;
        }

        $contact->email = request('email');
        $contact->save();

        $contact->users()->syncWithoutDetaching([\Auth::id() => ['name' => request('name')]]);

        $contact = $this->updateUser($contact);
        $this->syncSegment($contact);

        \DB::commit();

        return $contact;
    }

    /**
     * Delete contact
     * @param Contact $contact
     */
    public function delete(Contact $contact) : void
    {
        if ($contact->user_id) {
            throw ValidationException::withMessages(['message' => trans('global.associated_with_dependency', ['attribute' => trans('contact.contact'), 'dependency' => trans('user.user')])]);
        }
     
        if ($contact->users()->where('user_id', '!=', \Auth::id())->pluck('user_id')->all()) {
            $contact->users()->detach([\Auth::id()]);
        } else {
            $contact->delete();
        }
    }
}
