<?php

namespace App\Repositories;

use App\Models\Segment;
use App\Http\Resources\SegmentCollection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class SegmentRepository
{
    protected $segment;

    /**
     * Instantiate a new instance
     * @return void
     */
    public function __construct(
        Segment $segment
    ) {
        $this->segment = $segment;
    }

    /**
     * Get all segments
     */
    public function getAll() : SegmentCollection
    {
        return new SegmentCollection($this->segment->visibility()->get());
    }

    /**
     * Find segment with given id or throw an error
     * @param integer $id
     */
    public function findOrFail($id, $field = 'message') : Segment
    {
        $segment = $this->segment->visibility()->whereId($id)->first();

        if (! $segment) {
            throw ValidationException::withMessages([$field => __('global.could_not_find', ['attribute' => __('contact.segment.segment')])]);
        }

        return $segment;
    }

    /**
     * Find segment with given uuid or throw an error
     * @param uuid $uuid
     */
    public function findByUuidOrFail($uuid, $field = 'message') : Segment
    {
        $segment = $this->segment->visibility()->whereUuid($uuid)->first();

        if (! $segment) {
            throw ValidationException::withMessages([$field => __('global.could_not_find', ['attribute' => __('contact.segment.segment')])]);
        }

        return $segment;
    }

    /**
     * Find segment with given uuid or throw an error
     * 
     * @param array $uuids
     */
    public function findIdsByUuids($uuids) : array
    {
        return $this->segment->visibility()->whereIn('uuid', $uuids)->get()->pluck('id')->all();
    }

    /**
     * Filter by uuids
     *
     * @param array $uuids
     */
    public function filterByUuids($uuids = array()) : Collection
    {
        return $this->segment->with('contacts')->visibility()->whereIn('uuid', $uuids)->get();
    }

    /**
     * Paginate all segments
     */
    public function paginate() : SegmentCollection
    {
        $sort_by    = request()->query('sort_by', 'created_at');
        $order      = request()->query('order', 'desc');
        $name       = request()->query('name');

        $query = $this->segment->filterByName($name)->visibility();

        $per_page     = request('per_page', config('config.system.per_page'));
        $current_page = request('current_page');

        return new SegmentCollection($query->orderBy($sort_by, $order)->paginate((int) $per_page, ['*'], 'current_page'));
    }

    /**
     * Create a new segment
     */
    public function create() : Segment
    {
        $this->validateInput();

        $segment = $this->segment->firstOrNew(['name' => request('name')]);
        $segment->description = request('description');
        $segment->save();

        $segment->users()->attach(\Auth::id());

        return $segment;
    }

    /**
     * Validate input
     */
    private function validateInput() : void
    {
        $existing_segment_query = $this->segment->whereName(request('name'))->whereHas('users', function($q) {
            $q->where('user_id', \Auth::id());
        });

        if ($existing_segment_query->count()) {
            throw ValidationException::withMessages(['name' => trans('validation.unique', ['attribute' => trans('contact.segment.segment')])]);
        }
    }

    /**
     * Update given segment
     * @param Segment $segment
     */
    public function update(Segment $segment) : Segment
    {
        if ($segment->name == request('name')) {
            $segment->description = request('description');
            $segment->save();

            return $segment;
        }

        $this->validateInput();

        if ($segment->users()->where('user_id', '!=', \Auth::id())->pluck('user_id')->all()) {

            $new_segment = $this->segment->firstOrNew(['name' => request('name')]);
            $new_segment->description = request('description');
            $new_segment->save();

            $new_segment->users()->sync([\Auth::id()]);
            $segment->users()->detach([\Auth::id()]);

            return $new_segment;
        }

        $segment->name = request('name');
        $segment->description = request('description');
        $segment->save();

        return $segment;
    }

    /**
     * Delete segment
     * @param Segment $segment
     */
    public function delete(Segment $segment) : void
    {
        if ($segment->users()->where('user_id', '!=', \Auth::id())->pluck('user_id')->all()) {
            $segment->users()->detach([\Auth::id()]);
        } else {
            $segment->delete();
        }
    }
}
