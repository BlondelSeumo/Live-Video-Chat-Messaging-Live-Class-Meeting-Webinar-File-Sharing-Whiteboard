<?php
namespace App\Repositories\Site;

use App\Models\Site\Subscriber;
use App\Http\Resources\Site\SubscriberCollection;
use Illuminate\Validation\ValidationException;

class SubscriberRepository
{
    protected $subscriber;

    /**
     * Instantiate a new instance
     * @return void
     */
    public function __construct(
        Subscriber $subscriber
    ) {
        $this->subscriber = $subscriber;
    }

    /**
     * Find subscriber with given id or throw an error
     * @param integer $id
     */
    public function findOrFail($id, $field = 'message') : Subscriber
    {
        $subscriber = $this->subscriber->find($id);

        if (! $subscriber) {
            throw ValidationException::withMessages([$field => __('global.could_not_find', ['attribute' => __('site.subscriber.subscriber')])]);
        }

        return $subscriber;
    }

    /**
     * Find subscriber with given uuid or throw an error
     * @param uuid $uuid
     */
    public function findByUuidOrFail($uuid, $field = 'message') : Subscriber
    {
        $subscriber = $this->subscriber->filterByUuid($uuid)->first();

        if (! $subscriber) {
            throw ValidationException::withMessages([$field => __('global.could_not_find', ['attribute' => __('site.subscriber.subscriber')])]);
        }

        return $subscriber;
    }

    /**
     * Paginate all subscribers
     */
    public function paginate() : SubscriberCollection
    {
        $sort_by     = $this->subscriber->getSortBy();
        $order       = $this->subscriber->getOrder('asc');

        $query = $this->subscriber->filterByEmail(request()->query('email'));

        $per_page     = request('per_page', config('config.system.per_page'));
        $current_page = request('current_page');

        return new SubscriberCollection($query->orderBy($sort_by, $order)->paginate((int) $per_page, ['*'], 'current_page'));
    }

    /**
     * Delete subscriber
     * @param Subscriber $subscriber
     */
    public function delete(Subscriber $subscriber) : void
    {
        $subscriber->delete();
    }
}