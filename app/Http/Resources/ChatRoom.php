<?php

namespace App\Http\Resources;

use App\Helpers\CalHelper;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserSummary;
use App\Http\Resources\ChatCollection;

class ChatRoom extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $member = $this->getP2PMember();

        $my_membership = $this->chatRoomMembers->where('user_id', \Auth::id())->sortByDesc('joined_at')->first();

        $has_left = $my_membership->left_at ? true : false;
        $can_send_message = $my_membership->left_at ? true : false;
        $can_moderate = ($my_membership->is_owner || $my_membership->is_moderator) ? true : false;
        
        $members = array();
        if ($this->name && ! $has_left) {
            $members[] = $this->getMember($my_membership);

            foreach ($this->chatRoomMembers->where('user_id', '!=', \Auth::id())->where('left_at', null)->all() as $member) {
                $members[] = $this->getMember($member);
            }
        }

        return [
           'uuid'                 => $this->uuid,
           'name'                 => $this->name ?? optional($member)->name,
           'message'              => $this->when($this->message, $this->message),
           'member'               => $this->when(is_null($this->name), new UserSummary($member)),
           // 'members'              => $this->when($this->name, $members),
           'members'              => array(),
           'member_count'         => $this->when($this->relationLoaded('chatRoomMembers'), $this->chatRoomMembers->where('left_at', null)->count()),
           'can_send_message'     => $this->when($this->name, $can_send_message),
           'has_left'             => $this->when($this->name, $has_left),
           'can_moderate'         => $can_moderate,
           'is_group'             => is_null($this->name) ? false : true,
           'is_public_group'      => $this->getMeta('is_public_group') ? false : true,
           'chats'                => new ChatCollection($this->whenLoaded('chats')),
           'last_conversation_at' => CalHelper::toDateTime($this->last_conversation_at),
           'created_at'           => CalHelper::toDateTime($this->created_at),
           'updated_at'           => CalHelper::toDateTime($this->updated_at)
        ];
    }

    private function getMember($member)
    {
        return array(
            'user'         => new UserSummary($member->user),
            'username'     => optional($member->user)->username,
            'name'         => optional($member->user)->name,
            'joined_at'    => CalHelper::toDateTime($member->joined_at),
            'left_at'      => CalHelper::toDateTime($member->left_at),
            'is_owner'     => $member->is_owner ? true : false,
            'can_moderate' => ($member->is_owner || $member->is_moderator) ? true : false,
        );
    }

    private function getP2PMember()
    {
        if ($this->name) {
            return null;
        }

        if (! $this->relationLoaded('chatRoomMembers')) {
            return null;
        }

        return optional($this->chatRoomMembers->where('user_id', '!=', \Auth::id())->first())->user;
    }
}