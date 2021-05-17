<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site\Subscriber;
use Illuminate\Http\Request;
use App\Repositories\Site\SubscriberRepository;
use App\Http\Resources\Site\Subscriber as SubscriberResource;

class SubscriberController extends Controller
{
    protected $repo;

    /**
     * Instantiate a new controller instance
     * @return void
     */
    public function __construct(
       SubscriberRepository $repo
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
        $this->authorize('list', Subscriber::class);

        return $this->repo->paginate();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Subscriber  $subscriber
     * @return \Illuminate\Http\Response
     */
    public function show(Subscriber $subscriber)
    {
        $this->authorize('show', Subscriber::class);

        return new SubscriberResource($subscriber);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Subscriber  $subscriber
     * @return \Illuminate\Http\Response
     */
    public function destroy(Subscriber $subscriber)
    {
        $this->authorize('delete', Subscriber::class);

        $this->repo->delete($subscriber);

        return $this->success(['message' => __('global.deleted', ['attribute' => __('site.subscriber.subscriber')])]);
    }
}
