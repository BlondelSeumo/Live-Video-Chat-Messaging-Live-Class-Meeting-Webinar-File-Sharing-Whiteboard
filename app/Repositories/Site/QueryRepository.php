<?php
namespace App\Repositories\Site;

use App\Models\Site\Query;
use App\Http\Resources\Site\QueryCollection;
use Illuminate\Validation\ValidationException;

class QueryRepository
{
    protected $query;

    /**
     * Instantiate a new instance
     * @return void
     */
    public function __construct(
        Query $query
    ) {
        $this->query = $query;
    }

    /**
     * Find query with given id or throw an error
     * @param integer $id
     */
    public function findOrFail($id, $field = 'message') : Query
    {
        $query = $this->query->find($id);

        if (! $query) {
            throw ValidationException::withMessages([$field => __('global.could_not_find', ['attribute' => __('site.query.query')])]);
        }

        return $query;
    }

    /**
     * Find query with given uuid or throw an error
     * @param uuid $uuid
     */
    public function findByUuidOrFail($uuid, $field = 'message') : Query
    {
        $query = $this->query->filterByUuid($uuid)->first();

        if (! $query) {
            throw ValidationException::withMessages([$field => __('global.could_not_find', ['attribute' => __('site.query.query')])]);
        }

        return $query;
    }

    /**
     * Paginate all querys
     */
    public function paginate() : QueryCollection
    {
        $sort_by     = $this->query->getSortBy();
        $order       = $this->query->getOrder('asc');

        $query = $this->query->filterByName(request()->query('name'))->filterByEmail(request()->query('email'));

        $per_page     = request('per_page', config('config.system.per_page'));
        $current_page = request('current_page');

        return new QueryCollection($query->orderBy($sort_by, $order)->paginate((int) $per_page, ['*'], 'current_page'));
    }

    /**
     * Delete query
     * @param Query $query
     */
    public function delete(Query $query) : void
    {
        $query->delete();
    }
}