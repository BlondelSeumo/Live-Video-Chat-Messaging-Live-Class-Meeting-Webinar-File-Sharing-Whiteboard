<?php

namespace App\Repositories;

use App\Events\NewComment;
use App\Http\Resources\CommentCollection;
use App\Http\Resources\Media as MediaResource;
use App\Models\Comment;
use App\Models\Meeting;
use App\Traits\CustomMedia;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MeetingCommentRepository
{
    use CustomMedia;

    protected $meeting;
    protected $comment;

    /**
     * Instantiate a new instance
     * @return void
     */
    public function __construct(
        Meeting $meeting,
        Comment $comment
    ) {
        $this->meeting = $meeting;
        $this->comment = $comment;
    }

    /**
     * Paginate all meeting comments
     */
    public function paginate(Meeting $meeting) : CommentCollection
    {
        $meeting->isAccessible();

        $per_page = request('per_page', config('config.system.per_page'));
        
        $last_comment = request()->query('last_item_uuid') ? $this->comment->whereCommentableType('Meeting')->whereCommentableId($meeting->id)->whereUuid(request()->query('last_item_uuid'))->first() : null;

        $comments = $this->comment->with('user')->whereCommentableType('Meeting')->whereCommentableId($meeting->id)->when($last_comment, function ($q, $last_comment) {
            return $q->where('id', '<', $last_comment->id);
        })->orderBy('created_at', 'desc')->take($per_page)->get();


        return (new CommentCollection($comments))->additional(['meta' => [
            'per_page' => $per_page,
            'last_item_uuid' => optional($comments->last())->uuid
        ]]);
    }

    /**
     * Get meeting commment pre requisite
     */
    public function getPreRequisite() : array
    {
        return $this->mediaPreRequisite();
    }

    public function create(Meeting $meeting)
    {
        $meeting->ensureCommentable();

        $comment = $this->comment->forceCreate([
            'body' => request('body'),
            'user_id' => \Auth::user()->id,
            'commentable_type' => 'Meeting',
            'commentable_id' => $meeting->id
        ]);

        $comment->load('user');

        if (! request()->boolean('has_attachment')) {
            broadcast(new NewComment($meeting, $comment))->toOthers();
        }

        return $comment;
    }

    /**
     * Add media to meeting comment
     *
     * @param Comment $comment
     * @param Meeting $meeting
     */
    public function addMedia(Meeting $meeting, Comment $comment) : MediaResource
    {
        $meeting->ensureCommentable();

        $comment->isValid($meeting);

        $media = $comment
            ->addMediaFromRequest('file')
            ->sanitizingFileName(function ($fileName) {
                return strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
            })->toMediaCollection();
        
        if (request()->boolean('last_attachment')) {
            broadcast(new NewComment($meeting, $comment))->toOthers();
        }
        
        return new MediaResource($media);
    }

    /**
     * Remove media from meeting comment
     *
     * @param Comment $comment
     * @param Meeting $meeting
     */
    public function removeMedia(Meeting $meeting, Comment $comment, $uuid) : void
    {
        $meeting->ensureCommentable();

        $meeting->isValidComment($comment);

        $selected_media = $comment->getFirstMedia('default', function (Media $media) use ($uuid) {
            return $media->uuid == $uuid;
        });

        if (! $selected_media) {
            throw ValidationException::withMessages(['name' => __('global.could_not_find', ['attribute' => __('upload.attachment')])]);
        }

        $selected_media->delete();
    }
}
