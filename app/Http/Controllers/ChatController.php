<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChatRequest;
use App\Http\Requests\ChatRoomMemberRequest;
use App\Http\Requests\ChatRoomRequest;
use App\Repositories\ChatRepository;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    protected $repo;

    /**
     * Get chat pre requisite
     * @get ("/api/chat/pre-requisite")
     * @return array
     */
    public function preRequisite()
    {
        return $this->ok($this->repo->getPreRequisite());
    }

    /**
     * Instantiate a new controller instance
     * @return void
     */
    public function __construct(ChatRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Get chat rooms
     * @get ("/api/chat/rooms")
     * @return array
     */
    public function getRooms()
    {
        return $this->repo->getRooms();
    }

    /**
     * Get chat room
     * @get ("/api/chat/rooms/{uuid}")
     * @return array
     */
    public function getRoom($uuid)
    {
        return $this->repo->getRoom($uuid);
    }

    /**
     * Create chat room
     * @post ("/api/chat/rooms")
     * @return array
     */
    public function createRoom(ChatRoomRequest $request)
    {
        return $this->ok($this->repo->createRoom());
    }

    /**
     * Edit chat room
     * @patch ("/api/chat/rooms/{uuid}")
     * @return array
     */
    public function editRoom($uuid)
    {
        $chat_room = $this->repo->findRoomOrFail($uuid);

        $this->repo->validateGroupChat($chat_room);

        $this->repo->editRoom($chat_room);

        return $this->ok([]);
    }

    /**
     * List chat room members
     * @get ("/api/chat/rooms/{uuid}/members")
     * @return array
     */
    public function listMember($uuid)
    {
        $chat_room = $this->repo->findRoomOrFail($uuid);

        $this->repo->validateGroupChat($chat_room);

        return $this->ok($this->repo->listMember($chat_room));
    }

    /**
     * Sync public chat room members
     * @get ("/api/chat/rooms/{uuid}/sync")
     * @return array
     */
    public function syncMember($uuid)
    {
        $chat_room = $this->repo->findRoomOrFail($uuid);

        $this->repo->validateGroupChat($chat_room);

        $this->repo->syncMember($chat_room);

        return $this->ok([]);
    }

    /**
     * Add member to chat room
     * @post ("/api/chat/rooms/{uuid}/member")
     * @return array
     */
    public function addMember(ChatRoomMemberRequest $request, $uuid)
    {
        $chat_room = $this->repo->findRoomOrFail($uuid);

        $this->repo->validateGroupChat($chat_room);

        return $this->ok($this->repo->addMember($chat_room));
    }

    /**
     * Remove member to chat room
     * @post ("/api/chat/rooms/{uuid}/member")
     * @return array
     */
    public function removeMember(ChatRoomMemberRequest $request, $uuid)
    {
        $chat_room = $this->repo->findRoomOrFail($uuid);

        $this->repo->validateGroupChat($chat_room);

        return $this->ok($this->repo->removeMember($chat_room));
    }

    /**
     * Search chat user & rooms
     * @get ("/api/chat/search")
     * @return array
     */
    public function searchRoom()
    {
        return $this->ok($this->repo->searchRoom());
    }

    /**
     * Search chat messages
     * @get ("/api/chat/search/message")
     * @return array
     */
    public function searchMessage()
    {
        return $this->ok($this->repo->searchMessage());
    }

    /**
     * Get chat messages
     * @get ("/api/chat")
     * @return array
     */
    public function getMessage()
    {
        return $this->repo->getMessage();
    }

    /**
     * Store chat messages
     * @post ("/api/chat")
     * @return array
     */
    public function storeMessage(ChatRequest $request)
    {
        return $this->repo->storeMessage();
    }
}
