<?php
namespace App\Repositories;

use App\Models\Contact;
use App\Models\Segment;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

ini_set('max_execution_time', 0);

class ContactImportRepository
{
    protected $user;
    protected $contact;
    protected $segment;

    /**
     * Instantiate a new instance
     * @return void
     */
	public function __construct(
        User $user,
        Contact $contact,
        Segment $segment
	) {
        $this->user = $user;
        $this->contact = $contact;
        $this->segment = $segment;
	}

	protected $path = '/uploads/temp/contact-import/';

    /**
     * Get fiels for import
     */
	public function getOptions() : array
	{
        return array(
            [ 'name' => trans('contact.props.name'), 'uuid' => "name"],
            [ 'name' => trans('contact.props.email'), 'uuid' => "email"],
            [ 'name' => trans('contact.props.segment'), 'uuid' => "segment"],
        );
	}

    /**
     * Upload file for import
     */
    public function startImport() : array
    {
        $extension = request()->file('file')->getClientOriginalExtension();

        if ($extension != 'csv') {
            throw ValidationException::withMessages(['message' => trans('contact.import.csv_file_supported')]);
        }

    	$uuid = Str::uuid();
		\Storage::putFileAs($this->path, request()->file('file'), $uuid.'.csv');

        $path = request()->file('file')->getRealPath();
        $items = array_map('str_getcsv', file($path));

        if (count($items) > 500) {
            $this->deleteFile($uuid);
            throw ValidationException::withMessages(['message' => trans('contact.import.max_limit', ['number' => 500])]);
        }

        $items = array_slice($items, 1, 2);
        $options = $this->getOptions();

        return compact('items','options','uuid');
    }

    /**
     * Delete import file
     * 
     * @param uuid $uuid
     */
    private function deleteFile($uuid) : void
    {
        \Storage::delete($this->path.$uuid.'.csv');
    }

    /**
     * Finish import
     */
    public function finishImport() : void
    {
    	$uuid = request('uuid');
    	$all_columns = request('columns', []);

    	$columns = array();
    	foreach ($all_columns as $key => $value) {
            $selected = Arr::get($value, 'selected');
            $column_value = Arr::get($selected, 'uuid');

            if (! $column_value) {
                $this->deleteFile($uuid);
                throw ValidationException::withMessages(['message' => trans('contact.import.missing_column')]);
            }

    		array_push($columns, $column_value);
    	}

    	$options = array();
    	foreach ($this->getOptions() as $key => $value) {
    		array_push($options, Arr::get($value, 'uuid'));
    	}

    	if (count($columns) != count(array_unique($columns))) {
            $this->deleteFile($uuid);
    		throw ValidationException::withMessages(['message' => trans('contact.import.column_contains_duplicate_field')]);
    	}

    	if (count(array_diff($options, $columns))) {
            $this->deleteFile($uuid);
    		throw ValidationException::withMessages(['message' => trans('contact.import.invalid_column_found')]);
    	}

        if (! \Storage::exists($this->path.$uuid.'.csv')) {
            $this->deleteFile($uuid);
            throw ValidationException::withMessages(['message' => trans('contact.import.could_not_find_file')]);
        }

    	$items = array_map('str_getcsv', file(storage_path('app/'.$this->path.$uuid.'.csv')));

    	$existing_users = $this->user->get();
        $existing_contacts = $this->contact->get()->pluck('email')->all();
        $existing_segments = $this->segment->get();

        $contact_emails = [];

        foreach ($items as $index => $item) {
            if ($index == 0) {
                continue;
            }

            $email = Arr::get($item, array_search('email', $columns));

        	$contact_emails[] = $email;
        }

    	if (count($contact_emails) != count(array_unique($contact_emails))) {
            $this->deleteFile($uuid);
    		throw ValidationException::withMessages(['message' => trans('contact.import.duplicate_record')]);
        }
        
        activity()->disableLogging();

        \DB::beginTransaction();

        foreach ($items as $index => $item) {

            if ($index == 0)
                continue;

            $email   = Arr::get($item, array_search('email', $columns));
            $name    = Arr::get($item, array_search('name', $columns));
            $segment = Arr::get($item, array_search('segment', $columns));

            $segments = explode(',', $segment);

            $segment_ids = array();
            foreach ($segments as $segment) {
                $new_segment = $this->segment->firstOrCreate([
                    'name' => $segment
                ]);

                $new_segment->users()->syncWithoutDetaching([\Auth::id()]);

                if (! in_array($segment, $existing_segments->pluck('name')->all())) {
                    $existing_segments->push($new_segment);
                    $segment_ids[] = $new_segment->id;
                } else {
                    $segment_ids[] = $existing_segments->firstWhere('name', $segment)->id;
                }
            }

            $contact = $this->contact->firstOrCreate([
                'email' => $email
            ]);

            $contact->users()->syncWithoutDetaching([\Auth::id() => ['name' => request('name')]]);

            $user = $existing_users->firstWhere($email);
            $contact->user_id = $user ? $user->id : null;
            $contact->save();

            $contact->segments()->sync($segment_ids);
        }

        \DB::commit();

        $this->deleteFile($uuid);
        activity()->enableLogging();
        activity('import')->log('contact');
    }
}