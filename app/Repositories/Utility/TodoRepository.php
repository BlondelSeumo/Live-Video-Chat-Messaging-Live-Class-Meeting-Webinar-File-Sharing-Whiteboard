<?php
namespace App\Repositories\Utility;

use Carbon\Carbon;
use App\Helpers\ArrHelper;
use App\Helpers\CalHelper;
use Illuminate\Support\Str;
use App\Models\Utility\Todo;
use App\Http\Resources\Utility\TodoCollection;
use Illuminate\Validation\ValidationException;

class TodoRepository
{
    protected $todo;

    /**
     * Instantiate a new instance
     * @return void
     */
    public function __construct(
        Todo $todo
    ) {
        $this->todo = $todo;
    }

    /**
     * Find todo with given id or throw an error
     * @param integer $id
     */
    public function findOrFail($id, $field = 'message') : Todo
    {
        $todo = $this->todo->find($id);

        if (! $todo) {
            throw ValidationException::withMessages([$field => __('global.could_not_find', ['attribute' => __('utility.todo.todo')])]);
        }

        return $todo;
    }

    /**
     * Find todo with given uuid or throw an error
     * @param uuid $uuid
     */
    public function findByUuidOrFail($uuid, $field = 'message') : Todo
    {
        $todo = $this->todo->filterByUuid($uuid)->first();

        if (! $todo) {
            throw ValidationException::withMessages([$field => __('global.could_not_find', ['attribute' => __('utility.todo.todo')])]);
        }

        return $todo;
    }

    /**
     * Paginate all todos
     */
    public function paginate() : TodoCollection
    {
        $sort_by     = $this->todo->getSortBy();
        $order       = $this->todo->getOrder();
        $keyword     = request()->query('keyword');
        $status      = request()->query('status');
        $date        = request()->query('date');
        $start_date  = request()->query('start_date');
        $end_date    = request()->query('end_date');
        $today       = (bool) request()->query('today');

        $query = $this->todo->filterByUserId(\Auth::user()->id)->filterByKeyword($keyword)->filterCompleted($status)->dateBetween([
                'start_date' => $start_date,
                'end_date'   => $end_date
            ])->filterbyDueDate($date)->when($today, function ($q, $today) {
                return $q->filterbyDueDate(today());
            });
        
        if (request('type') == 'list') {
            $sort_by = explode(',', $sort_by);
            $order = explode(',', $order);
            foreach ($sort_by as $key => $s) {
                $query = $query->orderBy($s, $order[$key]);
            }
            return new TodoCollection($query->get());
        }

        $per_page     = request('per_page', config('config.system.per_page'));
        $current_page = request('current_page');

        return new TodoCollection($query->orderBy($sort_by, $order)->paginate((int) $per_page, ['*'], 'current_page'));
    }

    /**
     * Create a new todo
     */
    public function create() : Todo
    {
        return $this->todo->forceCreate($this->formatParams());
    }

    /**
     * Prepare given params for inserting into database
     * @param uuid $uuid
     */
    private function formatParams($uuid = null) : array
    {
        $formatted = [
            'title'       => request('title'),
            'description' => request('description'),
            'due_date'    => request('date', CalHelper::today()),
            'due_time'    => request('time') ? CalHelper::storeDateTime(request('date').' '.request('time')) : null
        ];

        if (! $uuid) {
            $formatted['user_id'] = \Auth::user()->id;
            $formatted['uuid']    = Str::uuid();
        }

        return $formatted;
    }

    /**
     * Update given todo
     * @param Todo $todo
     */
    public function update(Todo $todo) : Todo
    {
        $todo->forceFill($this->formatParams($todo->uuid))->save();

        return $todo;
    }

    /**
     * Delete todo
     * @param Todo $todo
     */
    public function delete(Todo $todo) : void
    {
        $todo->delete();
    }

    /**
     * Toggle given todo status
     * @param Todo $todo
     */
    public function toggle(Todo $todo) : Todo
    {
        $todo->forceFill([
            'completed_at' => (! $todo->status) ? Carbon::now() : null,
            'status'       => ! $todo->status
        ])->save();

        return $todo;
    }
}
