<?php

namespace App\Repositories;

use App\Models\Meeting;
use App\Http\Resources\Media as MediaResource;
use App\Traits\CustomMedia;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MeetingMediaRepository
{
    use CustomMedia;

    protected $meeting;

    /**
     * Instantiate a new instance
     * @return void
     */
    public function __construct(
        Meeting $meeting
    ) {
        $this->meeting = $meeting;
    }

    /**
     * Get meeting media pre requisite
     */
    public function getMediaPreRequisite() : array
    {
        return $this->mediaPreRequisite();
    }

    /**
     * Add media to meeting
     *
     * @param Meeting $meeting
     */
    public function addMedia(Meeting $meeting) : MediaResource
    {
        $meeting->ensureIsScheduled();

        $media = $meeting
            ->addMediaFromRequest('file')
            ->sanitizingFileName(function ($fileName) {
                return strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
            })->toMediaCollection();
        
        return new MediaResource($media);
    }

    /**
     * Remove media from meeting
     *
     * @param Meeting $meeting
     * @param uuid $uuid
     */
    public function removeMedia(Meeting $meeting, $uuid) : void
    {
        $meeting->ensureIsScheduled();

        $selected_media = $meeting->getFirstMedia('default', function (Media $media) use ($uuid) {
            return $media->uuid == $uuid;
        });

        if (! $selected_media) {
            throw ValidationException::withMessages(['name' => __('global.could_not_find', ['attribute' => __('upload.attachment')])]);
        }

        $selected_media->delete();
    }
}