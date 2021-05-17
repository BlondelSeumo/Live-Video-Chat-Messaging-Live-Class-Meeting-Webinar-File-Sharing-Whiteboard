<?php

namespace App\Http\Controllers\Utility;

use App\Models\Utility\Todo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Utility\TodoRequest;
use App\Repositories\Utility\TodoRepository;
use App\Http\Resources\Utility\Todo as TodoResource;

class TodoController extends Controller
{
    private $request;
    private $repo;

    /**
     * Instantiate a new controller instance
     * @return void
     */
    public function __construct(
        Request $request,
        TodoRepository $repo
    ) {
        $this->request  = $request;
        $this->repo     = $repo;

        $this->middleware('feature_available:todo');
    }

    /**
     * Get all todos
     * @get ("/api/utility/todos")
     * @return array
     */
    public function index()
    {
        $this->authorize('view', Todo::class);

        return $this->repo->paginate();
    }

    /**
     * Store todo
     * @post ("/api/utility/todos")
     * @param ({
     *      @Parameter("title", type="string", required="true", description="Todo title"),
     *      @Parameter("date", type="date", required="true", description="Todo due date"),
     * })
     * @return array
     */
    public function store(TodoRequest $request)
    {
        $this->authorize('create', Todo::class);

        $todo = $this->repo->create();

        $todo = new TodoResource($todo);

        return $this->success(['message' => __('global.added', ['attribute' => __('utility.todo.todo')]), 'todo' => $todo]);
    }

    /**
     * Get todo detail
     * @get ("/api/utility/todos/{uuid}")
     * @param ({
     *      @Parameter("uuid", type="uuid", required="true", description="Todo unique id"),
     * })
     * @return TodoResource
     */
    public function show($uuid)
    {
        $todo = $this->repo->findByUuidOrFail($uuid);

        $this->authorize('show', $todo);

        return new TodoResource($todo);
    }

    /**
     * Update todo status
     * @post ("/api/utility/todos/{uuid}/status")
     * @param ({
     *      @Parameter("uuid", type="uuid", required="true", description="Todo unique id"),
     * })
     * @return array
     */
    public function toggleStatus($uuid)
    {
        $todo = $this->repo->findByUuidOrFail($uuid);

        $this->authorize('update', $todo);

        $todo = $this->repo->toggle($todo);

        return $this->success(['message' => __('global.updated', ['attribute' => __('utility.todo.todo')])]);
    }

    /**
     * Update todo
     * @patch ("/api/utility/todos/{uuid}")
     * @param ({
     *      @Parameter("uuid", type="uuid", required="true", description="Todo unique id"),
     *      @Parameter("title", type="string", required="true", description="Todo title"),
     *      @Parameter("date", type="date", required="true", description="Todo date"),
     * })
     * @return array
     */
    public function update($uuid, TodoRequest $request)
    {
        $todo = $this->repo->findByUuidOrFail($uuid);

        $this->authorize('update', $todo);

        $todo = $this->repo->update($todo);

        return $this->success(['message' => __('global.updated', ['attribute' => __('utility.todo.todo')])]);
    }

    /**
     * Delete todo
     * @delete ("/api/utility/todos/{uuid}")
     * @param ({
     *      @Parameter("uuid", type="uuid", required="true", description="Todo unique id"),
     * })
     * @return array
     */
    public function destroy($uuid)
    {
        $todo = $this->repo->findByUuidOrFail($uuid);

        $this->authorize('delete', $todo);

        $this->repo->delete($todo);

        return $this->success(['message' => __('global.deleted', ['attribute' => __('utility.todo.todo')])]);
    }
}
