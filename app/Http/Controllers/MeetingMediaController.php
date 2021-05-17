<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Meeting;
use App\Repositories\MeetingMediaRepository;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MeetingMediaController extends Controller
{
    protected $repo;

    /**
     * Instantiate a new controller instance
     * @return void
     */
    public function __construct(
        MeetingMediaRepository $repo
    ) {
        $this->repo = $repo;
    }

    /**
     * Get meeting media pre requisite
     * @get ("/api/meetings/{meeting}/media/pre-requisite")
     * @return array
     */
    public function mediaPreRequisite()
    {
        $this->authorize('list', Meeting::class);
        
        return $this->ok($this->repo->getMediaPreRequisite());
    }

    /**
     * Add media to meeting
     * @post ("/api/meetings/{uuid}/media")
     * @param ({
     *      @Parameter("uuid", type="uuid", required="true", description="Meeting unique id"),
     * })
     * @return array
     */
    public function addMedia(Meeting $meeting)
    {
        $this->authorize('list', Meeting::class);

        $meeting->isAccessible(true);

        $media = $this->repo->addMedia($meeting);

        return $this->success(['message' => __('global.added', ['attribute' => __('upload.attachments')]), 'upload' => $media]);
    }

    /**
     * Remove media from meeting
     * @delete ("/api/meetings/{uuid}/media")
     * @param ({
     *      @Parameter("uuid", type="uuid", required="true", description="Meeting unique id"),
     * })
     * @return array
     */
    public function removeMedia(Meeting $meeting, $uuid)
    {
        $this->authorize('list', Meeting::class);

        $meeting->isAccessible(true);

        $this->repo->removeMedia($meeting, $uuid);

        return $this->success(['message' => __('global.deleted', ['attribute' => __('upload.attachments')])]);
    }

    /**
     * Download media from meeting or meeting comment
     * @delete ("/meetings/{meeting}/downloads/{media}")
     * @param ({
     *      @Parameter("meeting", type="uuid", required="true", description="Meeting unique id"),
     *      @Parameter("media", type="uuid", required="true", description="Media unique id"),
     * })
     * @return array
     */
    public function download(Meeting $meeting, $media)
    {
        $this->authorize('list', Meeting::class);

        $meeting->isAccessible();

        $media = Media::whereUuid($media)->firstOrFail();

        if ($media->model instanceof Meeting && $media->model_id != $meeting->id) {
            throw new FileNotFoundException();
        } else if ($media->model instanceof Comment && ! in_array($media->model_id, $meeting->comments()->pluck('id')->all())) {
            throw new FileNotFoundException();
        }

        return $media;
    }
}