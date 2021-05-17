<?php
namespace App\Repositories;

use App\Http\Resources\OptionCollection;
use App\Models\Option;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OptionRepository
{
    protected $option;

    /**
     * Instantiate a new instance
     * @return void
     */
    public function __construct(
        Option $option
    ) {
        $this->option = $option;
    }

    /**
     * Find option with given id or throw an error
     * @param integer $id
     */
    public function findOrFail($id, $field = 'message') : Option
    {
        $option = $this->option->find($id);

        if (! $option) {
            throw ValidationException::withMessages([$field => __('global.could_not_find', ['attribute' => __('option.option')])]);
        }

        return $option;
    }

    /**
     * Find option with given uuid or throw an error
     * @param uuid $uuid
     */
    public function findByUuidOrFail($uuid, $field = 'message') : Option
    {
        $option = $this->option->filterByUuid($uuid)->first();

        if (! $option) {
            throw ValidationException::withMessages([$field => __('global.could_not_find', ['attribute' => __('option.option')])]);
        }

        return $option;
    }

    /**
     * Get all filtered data
     */
    public function getData($params = array())
    {
        $sort_by = request('sort_by', 'created_at');
        $order   = request('order', 'desc');

        $name = Arr::get($params, 'name', request('name'));
        $type = Arr::get($params, 'type', request('type'));
        $slug = Arr::get($params, 'slug', request('slug'));

        return $this->option->filterByName($name)->filterByType($type)->filterBySlug($slug)->orderBy($sort_by, $order);
    }

    /**
     * List all options using given params.
     */

    public function list($params = array()) : OptionCollection
    {
        return new OptionCollection($this->getData($params)->get());
    }

    /**
     * List all options using given params.
     */

    public function listOrderedByName($params = array()) : OptionCollection
    {
        $params['sort_by'] = 'name';
        $params['order'] = 'asc';
        
        return new OptionCollection($this->getData($params)->get());
    }

    /**
     * Paginate all options
     */
    public function paginate() : OptionCollection
    {
        $per_page     = request('per_page', config('config.system.per_page'));
        $current_page = request('current_page');

        return new OptionCollection($this->getData()->paginate((int) $per_page, ['*'], 'current_page'));
    }

    /**
     * Create a new option
     */
    public function create() : Option
    {
        return $this->option->forceCreate($this->formatParams());
    }

    /**
     * Prepare given params for inserting into database
     * @param uuid $uuid
     */
    private function formatParams($uuid = null) : array
    {
        $formatted = [
            'name'        => request('name'),
            'description' => request('description'),
            'slug'        => request('slug'),
            'type'        => request('type'),
            'parent_id'   => null
        ];

        $parent = request('parent');
        $parent = Arr::get($parent, 'uuid');

        if ($parent) {
            $parent = $this->findByUuidOrFail($parent);
            $formatted['parent_id'] = $parent->id != $uuid ? $parent->id : null;
        }

        if (! $uuid) {
            $formatted['uuid'] = Str::uuid();
        }

        return $formatted;
    }

    /**
     * Update given option
     * @param Option $option
     */
    public function update(Option $option) : Option
    {
        $option->forceFill($this->formatParams($option->uuid))->save();

        return $option;
    }

    /**
     * Delete option
     * @param Option $option
     */
    public function delete(Option $option) : void
    {
        $option->delete();
    }

    /**
     * Get pre requisite.
     *
     * @return Array
     */
    public function getPreRequisite()
    {
        $options = $this->list();

        return compact('options');
    }
}
