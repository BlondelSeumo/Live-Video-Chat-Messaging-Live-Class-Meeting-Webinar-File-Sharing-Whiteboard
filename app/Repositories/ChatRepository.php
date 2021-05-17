<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Chat;
use App\Models\ChatRoom;
use Illuminate\Support\Arr;
use App\Enums\Auth\UserStatus;
use App\Events\ChatMessageSentToRoom;
use App\Events\ChatMessageSentToUser;
use App\Http\Resources\ChatCollection;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\Chat as ChatResource;
use App\Http\Resources\ChatRoom as ChatRoomResource;
use App\Http\Resources\ChatRoomCollection;
use App\Http\Resources\ChatRoomMemberCollection;
use App\Http\Resources\UserSummaryCollection;
use App\Models\ChatRoomMember;
use App\Traits\CustomMedia;

class ChatRepository
{
    use CustomMedia;

    protected $chat_room_member;
    protected $chat_room;
    protected $chat;
    protected $user;

    /**
     * Instantiate a new instance
     * @return void
     */
    public function __construct(
        ChatRoomMember $chat_room_member,
        ChatRoom $chat_room,
        Chat $chat,
        User $user
    ) {
        $this->chat_room_member = $chat_room_member;
        $this->chat_room = $chat_room;
        $this->chat = $chat;
        $this->user = $user;
    }

    /**
     * Get chat pre requisite
     */
    public function getPreRequisite() : array
    {
        return $this->mediaPreRequisite();
    }

    /**
     * Get chat rooms
     */
    public function getRooms()
    {
        $per_page = request('per_page', config('config.system.per_page'));
        
        $last_chat_room = request()->query('last_item_uuid') ? $this->chat_room->whereHas('chatRoomMembers', function ($q) {
            $q->where('user_id', '=', \Auth::id());
        })->whereUuid(request()->query('last_item_uuid'))->first() : null;

        $chat_rooms = $this->chat_room->with([
            'chatRoomMembers',
            'chatRoomMembers.user'
        ])->withLastMessage()->whereHas('chatRoomMembers', function ($q) {
            $q->where('user_id', '=', \Auth::id());
        })->when($last_chat_room, function ($q, $last_chat_room) {
            return $q->where('id', '<', $last_chat_room->id);
        })->orderBy('last_conversation_at', 'desc')->take($per_page)->get();

        return (new ChatRoomCollection($chat_rooms))->additional([
            'meta' => [
                'per_page' => $per_page,
                'last_item_uuid' => optional($chat_rooms->last())->uuid
            ]
        ]);
    }

    /**
     * Find chat room or fail
     *
     * @param string $uuid
     */
    public function findRoomOrFail($uuid) : ChatRoom
    {
        $chat_room = $this->chat_room->with([
            'chatRoomMembers',
            'chatRoomMembers.user'
        ])->whereUuid($uuid)->whereHas('chatRoomMembers', function ($q) {
            $q->where('user_id', '=', \Auth::id());
        })->first();

        if (! $chat_room) {
            throw ValidationException::withMessages(['message' => trans('general.invalid_action')]);
        }

        return $chat_room;
    }

    /**
     * Get chat room
     *
     * @param uuid $uuid
     */
    public function getRoom($uuid) : array
    {
        $chat_room = $this->findRoomOrFail($uuid);

        $query = $this->chat->with('user')->whereChatRoomId($chat_room->id);

        if ($chat_room->name) {
            $query->where(function($q) use ($chat_room) {
                foreach ($chat_room->chatRoomMembers->where('user_id', \Auth::id())->all() as $member) {
                    $q->orWhere(function($q1) use ($member) {
                        $q1->where('created_at', '>=', $member->joined_at)->where('created_at', '<=', $member->left_at ? : now());
                    });
                }
            });
        }
        
        $messages = $query->orderBy('created_at', 'desc')->orderBy('id', 'desc')->take(10)->get();

        $chat_room = new ChatRoomResource($chat_room);
        $chats = new ChatCollection($messages);

        return compact('chat_room', 'chats');
    }

    /**
     * Validate if chat room is group chat
     *
     * @param ChatRoom $chat_room
     */
    public function validateGroupChat(ChatRoom $chat_room) : void
    {
        if (! $chat_room->name) {
            throw ValidationException::withMessages(['message' => trans('general.invalid_action')]);
        }
    }

    /**
     * Create chat room
     */
    public function createRoom()
    {
        $members = Arr::pluck(request('members'), 'uuid');

        $query = $this->user->where('id', '!=', \Auth::id())->whereStatus(UserStatus::ACTIVATED);

        if (\Auth::user()->hasRole('admin') && request('is_public_group')) {} 
        else {
            $query->whereIn('uuid', $members);
        }

        $users = $query->get();

        // if (! $users->count()) {
        //     throw ValidationException::withMessages(['message' => trans('global.could_not_find', ['attribute' => trans('user.user')])]);
        // }

        $chat_room = $this->chat_room->forceCreate([
            'name' => request('name'),
            'last_conversation_at' => now(),
            'meta' => [
                'is_public_group' => (\Auth::user()->hasRole('admin') && request('is_public_group')) ? true : false
            ]
        ]);

        $chat_room->chatRoomMembers()->create([
            'user_id' => \Auth::id(),
            'joined_at' => now(),
            'is_owner' => true
        ]);

        $users->each(function ($user) use ($chat_room) {
            $chat_room->chatRoomMembers()->create([
                'user_id' => $user->id,
                'joined_at' => now()
            ]);
        });

        $chat_room->load([
            'chatRoomMembers', 
            'chatRoomMembers.user'
        ]);

        return new ChatRoomResource($chat_room);
    }

    /**
     * Add members to chat room
     *
     * @param ChatRoom $chat_room
     */
    public function addMember(ChatRoom $chat_room)
    {
        $members = Arr::pluck(request('members'), 'uuid');

        $chat_room_members = $chat_room->chatRoomMembers()->where('left_at', null)->pluck('user_id')->all();

        $users = $this->user->whereIn('uuid', $members)->whereNotIn('id', $chat_room_members)->whereStatus(UserStatus::ACTIVATED)->get();

        if (! $users->count()) {
            throw ValidationException::withMessages(['message' => trans('global.could_not_find', ['attribute' => trans('user.user')])]);
        }

        $users->each(function ($user) use ($chat_room) {
            $chat_room->chatRoomMembers()->create([
                'user_id' => $user->id,
                'joined_at' => now()
            ]);
        });

        $chat_room->load([
            'chatRoomMembers', 
            'chatRoomMembers.user'
        ]);

        return new ChatRoomResource($chat_room);
    }

    /**
     * Sync members to public chat room
     *
     * @param ChatRoom $chat_room
     */
    public function syncMember(ChatRoom $chat_room)
    {
        $user_ids = $chat_room->chatRoomMembers->pluck('user_id')->all();

        $users = $this->user->whereNotIn('id', $user_ids)->whereStatus(UserStatus::ACTIVATED)->get();

        $users->each(function ($user) use ($chat_room) {
            $chat_room->chatRoomMembers()->create([
                'user_id' => $user->id,
                'joined_at' => now()
            ]);
        });
    }

    /**
     * List members of chat room
     *
     * @param ChatRoom $chat_room
     */
    public function listMember(ChatRoom $chat_room)
    {
        $q = request('q');
        $per_page = request('per_page', config('config.system.per_page'));

        $users = $this->chat_room_member->with('user')->whereChatRoomId($chat_room->id)->when($q, function($query, $q) {
            $query->whereHas('user', function($query1) use ($q) {
                $query1->where('name', 'like', '%'.$q.'%')->orWhere('email', 'like', '%'.$q.'%');
            });
        })->select('chat_room_members.*', \DB::raw('(SELECT name FROM users WHERE users.id = chat_room_members.user_id) as sort_by'))->orderBy('sort_by', 'asc')->get();

        return new ChatRoomMemberCollection($users);
    }

    /**
     * Remove members from chat room
     *
     * @param ChatRoom $chat_room
     */
    public function removeMember(ChatRoom $chat_room)
    {
        $members = Arr::pluck(request('members'), 'uuid');

        $users = $this->user->whereIn('uuid', $members)->whereStatus(UserStatus::ACTIVATED)->get();

        foreach ($users as $user) {
            $this->chat_room_member->whereChatRoomId($chat_room->id)->where('user_id', $user->id)->where('left_at', null)->where('is_owner', 0)->update(['left_at' => now()]);
        }

        $chat_room->load([
            'chatRoomMembers', 
            'chatRoomMembers.user'
        ]);

        return new ChatRoomResource($chat_room);
    }

    /**
     * Edit chat room
     *
     * @param ChatRoom $chat_room
     */
    public function editRoom(ChatRoom $chat_room) : void
    {
        request()->validate([
            'name' => 'required|min:3'
        ]);

        $owner = $this->chat_room_member->whereChatRoomId($chat_room->id)->where('user_id', \Auth::id())->where('is_owner', 1)->first();

        if (! $owner) {
            throw ValidationException::withMessages(['message' => trans('user.permission_denied')]);
        }

        $chat_room->name = request('name');
        $chat_room->save();
    }

    /**
     * Search chat room
     */
    public function searchRoom()
    {
        if (! strlen(request('q'))) {
            return [];
        }

        $users = new UserSummaryCollection($this->user->where('name', 'like', '%'.request('q').'%')->where('id', '!=', \Auth::id())->whereStatus(UserStatus::ACTIVATED)->get());

        $rooms = new ChatRoomCollection($this->chat_room->withLastMessage()->with([
            'chatRoomMembers',
            'chatRoomMembers.user'
        ])->whereHas('chatRoomMembers', function($q0) {
            $q0->where('user_id', \Auth::id());
        })->where(function ($q) {
            $q->where('name', 'like', '%'.request('q').'%')->orWhereHas('chatRoomMembers', function ($q1) {
                $q1->whereHas('user', function ($q2) {
                    $q2->where('name', 'like', '%'.request('q').'%');
                });
            });
        })->get());

        return compact('users', 'rooms');
    }

    /**
     * Search chat message
     */
    public function searchMessage()
    {
        $chat_room = $this->getChatRoom(request()->query('chat_room'));

        $query = $this->chat->with('user')->whereHas('chatRoom', function ($q) use ($chat_room) {
            $q->where('id', $chat_room->id);
        })->where('message', 'like', '%'.request('q').'%');

        if ($chat_room->name) {
            $query->where(function($q) use ($chat_room) {
                foreach ($chat_room->chatRoomMembers->where('user_id', \Auth::id())->all() as $member) {
                    $q->orWhere(function($q1) use ($member) {
                        $q1->where('created_at', '>=', $member->joined_at)->where('created_at', '<=', $member->left_at ? : now());
                    });
                }
            });
        }

        return new ChatCollection($query->orderBy('created_at', 'desc')->get());
    }

    /**
     * Get chat message
     */
    public function getMessage()
    {
        if (request()->query('chat_room')) {
            $chat_room = $this->getChatRoom(request()->query('chat_room'));

            $chat_room->load('chatRoomMembers');

        } else {
            $user = $this->getUser(request()->query('user'));
            $chat_room = $this->getChatRoomFromUser($user);
        }

        $messages = [];

        $per_page = request('per_page', config('config.system.per_page'));

        if(! $chat_room) {
            return (new ChatCollection($messages))->additional(['meta' => [
                'chat_room' => null,
                'per_page' => $per_page,
                'last_item_uuid' => null
            ]]);
        }

        $last_chat = request()->query('last_item_uuid') ? $this->chat->whereChatRoomId($chat_room->id)->whereUuid(request()->query('last_item_uuid'))->first() : null;

        $query = $this->chat->with('user')->whereChatRoomId($chat_room->id)->when($last_chat, function ($q, $last_chat) {
            return $q->where('id', '<', $last_chat->id);
        });

        if ($chat_room->name) {
            $query->where(function($q) use ($chat_room) {
                foreach ($chat_room->chatRoomMembers->where('user_id', \Auth::id())->all() as $member) {
                    $q->orWhere(function($q1) use ($member) {
                        $q1->where('created_at', '>=', $member->joined_at)->where('created_at', '<=', $member->left_at ? : now());
                    });
                }
            });
        }
        
        $messages = $query->orderBy('created_at', 'desc')->orderBy('id', 'desc')->take($per_page)->get();

        return (new ChatCollection($messages))->additional(['meta' => [
            'chat_room' => new ChatRoomResource($chat_room),
            'per_page' => $per_page,
            'last_item_uuid' => optional($messages->last())->uuid
        ]]);
    }

    /**
     * Store chat message
     */
    public function storeMessage()
    {
        if (! request()->file('file')) {
            request()->validate([
                'message' => 'required'
            ]);
        }

        if (request('user')) {
            $user_uuid = Arr::get(request('user'), 'uuid');
            $user = $this->getUser($user_uuid);

            $chat_room = $this->getChatRoomFromUser($user);
        } else if (request('chat_room')) {
            $chat_room_uuid = Arr::get(is_string(request('chat_room')) ? json_decode(request('chat_room'), true) : request('chat_room'), 'uuid');

            $chat_room = $this->getChatRoom($chat_room_uuid);

            if (! $chat_room) {
                throw ValidationException::withMessages(['message' => trans('global.could_not_find', ['attribute' => trans('chat.chat_room')])]);
            }
            
            $my_membership = $chat_room->chatRoomMembers->where('user_id', \Auth::id())->sortByDesc('joined_at')->first();

            if ($my_membership && $my_membership->left_at) {
                throw ValidationException::withMessages(['message' => trans('general.invalid_action')]);
            }
        }

        if (! $chat_room) {
            $chat_room = $this->chat_room->forceCreate([
                'meta' => []
            ]);

            collect([$user->id, \Auth::id()])->each(function ($item) use ($chat_room) {
                $chat_room->chatRoomMembers()->create([
                    'user_id' => $item,
                    'joined_at' => now()
                ]);
            });
        }

        $chat = $this->chat->forceCreate([
            'chat_room_id' => $chat_room->id,
            'message' => request('message'),
            'user_id' => \Auth::id()
        ]);

        if (request()->file('file')) {
            $media = $chat
            ->addMediaFromRequest('file')
            ->sanitizingFileName(function ($fileName) {
                return strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
            })->toMediaCollection();

            $chat->message = '%%chat_attachment_%%';
            $chat->save();
        }

        $chat_room->last_conversation_at = now();
        $chat_room->save();

        broadcast(new ChatMessageSentToRoom($chat))->toOthers();
        broadcast(new ChatMessageSentToUser($chat, $chat_room))->toOthers();

        $chat->load([
            'chatRoom', 
            'chatRoom.chatRoomMembers', 
            'chatRoom.chatRoomMembers.user', 
            'user'
        ]);
        
        return (new ChatResource($chat))->additional([
            'meta' => [
                'request_uuid' => request()->has('file') ? request('request_uuid') : null
            ]
        ]);
    }

    /**
     * Get chat user
     * @param uuid $user_uuid
     */
    private function getUser($user_uuid = null) : User
    {
        $user = $this->user->whereUuid($user_uuid)->whereStatus(UserStatus::ACTIVATED)->first();

        if (! $user) {
            throw ValidationException::withMessages(['message' => trans('global.could_not_find', ['attribute' => trans('user.user')])]);
        }

        if ($user->id === \Auth::id()) {
            throw ValidationException::withMessages(['message' => trans('general.invalid_action')]);
        }

        return $user;
    }

    /**
     * Get chat room from user
     */
    private function getChatRoomFromUser(User $user)
    {
        return $this->chat_room->whereNull('name')->whereHas('chatRoomMembers', function ($q) use ($user) {
            $q->whereIn('user_id', [$user->id, \Auth::id()]);
        }, '=', 2)->first();
    }

    /**
     * Get chat room
     * @param uuid $chat_room_uuid
     */
    private function getChatRoom($chat_room_uuid = null) : ?ChatRoom
    {
        return $this->chat_room->whereUuid($chat_room_uuid)->whereHas('chatRoomMembers', function ($q) {
            $q->where('user_id', \Auth::id());
        })->first();
    }
}
