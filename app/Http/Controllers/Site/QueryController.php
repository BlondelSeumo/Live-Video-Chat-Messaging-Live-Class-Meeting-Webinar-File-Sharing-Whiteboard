<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site\Query;
use Illuminate\Http\Request;
use App\Repositories\Site\QueryRepository;
use App\Http\Resources\Site\Query as QueryResource;

class QueryController extends Controller
{
    protected $repo;

    /**
     * Instantiate a new controller instance
     * @return void
     */
    public function __construct(
       QueryRepository $repo
    ) {
        $this->repo = $repo;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('list', Query::class);

        return $this->repo->paginate();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Query  $query
     * @return \Illuminate\Http\Response
     */
    public function show(Query $query)
    {
        $this->authorize('show', Query::class);

        return new QueryResource($query);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Query  $query
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Query $query)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Query  $query
     * @return \Illuminate\Http\Response
     */
    public function destroy(Query $query)
    {
        $this->authorize('delete', Query::class);

        $this->repo->delete($query);

        return $this->success(['message' => __('global.deleted', ['attribute' => __('site.query.query')])]);
    }
}
