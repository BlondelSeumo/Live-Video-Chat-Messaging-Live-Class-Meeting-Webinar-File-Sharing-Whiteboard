<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\Comment;
use App\Http\Resources\Comment as CommentResource;
use App\Repositories\MeetingCommentRepository;

class MeetingCommentController extends Controller
{
    protected $repo;

    /**
     * Instantiate a new controller instance
     * @return void
     */
    public function __construct(
        MeetingCommentRepository $repo
    ) {
        $this->repo = $repo;
    }

    /**
     * Get meeting comment pre requisite
     * @get ("/api/meetings/{meeting}/comment/pre-requisite")
     * @return array
     */
    public function preRequisite()
    {
        $this->authorize('list', Meeting::class);
        
        return $this->ok($this->repo->getPreRequisite());
    }

    /**
     * Get all meeting comments
     * @get ("/api/meetings/{meeting}/comment")
     * @return array
     */
    public function index(Meeting $meeting)
    {
        $this->authorize('list', Meeting::class);

        return $this->repo->paginate($meeting);
    }

    /**
     * Store comment
     * @post ("/api/meetings/{meeting}/comments")
     * @param ({
     *      @Parameter("body", type="string", required="true", description="Comment body"),
     * })
     * @return array
     */
    public function store(Meeting $meeting)
    {
        $this->authorize('list', Meeting::class);
        
        $comment = $this->repo->create($meeting);

        return $this->success(['message' => __('global.posted', ['attribute' => __('comment.comment')]), 'comment' => new CommentResource($comment)]);
    }

    /**
     * Add media to meeting comment
     * @post ("/api/meetings/{uuid}/comment/{comment}/media")
     * @param ({
     *      @Parameter("meeting", type="uuid", required="true", description="Meeting unique id"),
     *      @Parameter("comment", type="uuid", required="true", description="Meeting comment unique id"),
     * })
     * @return array
     */
    public function addMedia(Meeting $meeting, Comment $comment)
    {
        $this->authorize('list', Meeting::class);

        $media = $this->repo->addMedia($meeting, $comment);

        return $this->success(['message' => __('global.added', ['attribute' => __('upload.attachments')]), 'upload' => $media]);
    }

    /**
     * Remove media from meeting comment
     * @delete ("/api/meetings/{uuid}/comments/{comment}/media/{uuid}")
     * @param ({
     *      @Parameter("meeting", type="uuid", required="true", description="Meeting unique id"),
     *      @Parameter("comment", type="uuid", required="true", description="Meeting comment unique id"),
     *      @Parameter("uuid", type="uuid", required="true", description="Meeting comment media unique id"),
     * })
     * @return array
     */
    public function removeMedia(Meeting $meeting, Comment $comment, $uuid)
    {
        $this->authorize('list', Meeting::class);

        $this->repo->removeMedia($meeting, $comment, $uuid);

        return $this->success(['message' => __('global.deleted', ['attribute' => __('upload.attachments')])]);
    }
}
