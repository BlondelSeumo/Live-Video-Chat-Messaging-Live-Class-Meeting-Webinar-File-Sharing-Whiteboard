<?php

namespace App\Observers;

use App\Enums\Auth\UserStatus;
use App\Models\ChatRoom;
use App\Models\ChatRoomMember;
use App\Models\Contact;
use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function created(User $user)
    {
        $this->updateUsercontact($user);
        $this->addUserToPublicChatGroup($user);
    }

    /**
     * Handle the User "updated" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function updated(User $user)
    {
        $this->updateUsercontact($user);
        $this->addUserToPublicChatGroup($user);
    }

    /**
     * Handle the User "deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function deleted(User $user)
    {
        //
    }

    /**
     * Handle the User "forceDeleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        //
    }

    private function addUserToPublicChatGroup($user)
    {
        if ($user->status != UserStatus::ACTIVATED) {
              return;
        }

        $chat_rooms = ChatRoom::where('meta->is_public_group', true)->get();

        foreach ($chat_rooms as $chat_room) {
            $member = ChatRoomMember::firstOrCreate([
                'chat_room_id' => $chat_room->id,
                'user_id' => $user->id
            ]);

            $member->joined_at = $member->joined_at ? : now();
            $member->save();
        }
    }

    private function updateUsercontact(User $user)
    {
        $contact = Contact::whereEmail($user->email)->first();

        if ($contact && ! $contact->user_id) {
            $contact->user_id = $user->id;
            $contact->save();
        }
    }
}
