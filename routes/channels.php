<?php
use App\Helpers\ArrHelper;
use App\Helpers\CalHelper;
use App\Models\ChatRoom;
use Illuminate\Support\Facades\Broadcast;
use App\Models\Meeting;
use App\Enums\MeetingStatus;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Broadcast::channel('User.{uuid}', function ($user, $uuid) {
//     return $user && $user->uuid === $uuid;
// });

//Private Channel for loggedin users
Broadcast::channel('announcements', function ($user) {
    return \Auth::check();
});

//Presence Channel for loggedin users
Broadcast::channel('Users', function ($user) {
    if (! \Auth::check()) {
        return false;
    }

    $gender = ArrHelper::searchByKey(ArrHelper::getTransList('genders'), 'uuid', $user->gender);

    return [
        'uuid'       => $user->uuid,
        'username'   => $user->username,
        'name'       => $user->name,
        'avatar'     => $user->avatar,
        'gender'     => $gender,
        'birth_date' => CalHelper::toDate($user->birth_date),
        'age'        => CalHelper::getAge($user->birth_date),
        'busy'       => false
    ];
});

//Private Channel for each loggedin user
Broadcast::channel('User.{uuid}', function ($user, $uuid) {
    return \Auth::check() && $user && $user->uuid ? true : false;
});

//Presence Channel for each chat room
Broadcast::channel('ChatRoom.{uuid}', function ($user, $uuid) {
    if (! \Auth::check()) {
        return false;
    }

    $chat_room = ChatRoom::with('chatRoomMembers')->whereUuid($uuid)->whereHas('chatRoomMembers', function ($q) {
        $q->where('user_id', '=', \Auth::id());
    })->first();

    if (! $chat_room) {
        return false;
    }

    return [
        'uuid'       => $user->uuid,
        'username'   => $user->username,
        'name'       => $user->name
    ];
});

//Presence Channel for each meeting
Broadcast::channel('Meeting.{uuid}', function ($user, $uuid) {
    $meeting = Meeting::filterByUuid($uuid)->first();

    if (!$meeting) {
        return null;
    }

    if (! \Auth::check()) {
        return false;
    }

    return ['uuid' => $user->uuid, 'username' => $user->username, 'name' => $user->name];
});

//Public Channel for each meeting
Broadcast::channel('MeetingGuest.{uuid}', function ($uuid) {

    $meeting = Meeting::filterByUuid($uuid)->first();

    if (!($meeting && $meeting->getMeta('is_pam') && $meeting->getMeta('status') !== MeetingStatus::CANCELLED && $meeting->getMeta('status') !== MeetingStatus::ENDED)) {
        return null;
    }

    if (\Auth::check()) {
        return ['uuid' => \Auth::user()->uuid, 'username' => \Auth::user()->username, 'name' => \Auth::user()->name];
    }

    return ['isPam' => true];
});
