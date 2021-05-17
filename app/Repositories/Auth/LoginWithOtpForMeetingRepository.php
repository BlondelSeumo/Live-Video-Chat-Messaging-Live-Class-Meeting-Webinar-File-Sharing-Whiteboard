<?php

namespace App\Repositories\Auth;

use App\Models\User;
use App\Models\Contact;
use App\Models\Meeting;
use App\Traits\EmailOtp;
use Illuminate\Support\Str;
use App\Enums\MeetingStatus;
use App\Enums\Auth\UserStatus;
use App\Http\Resources\AuthUser;
use App\Repositories\InviteeRepository;
use Illuminate\Validation\ValidationException;

class LoginWithOtpForMeetingRepository
{
    use EmailOtp;

    protected $user;
    protected $contact;
    protected $invitee;

    /**
     * Instantiate a new instance
     * @return void
     */
    public function __construct(
        User $user,
        Contact $contact,
        InviteeRepository $invitee
    ) {
        $this->user = $user;
        $this->contact = $contact;
        $this->invitee = $invitee;
    }

    /**
     * Generate Email OTP
     */
    public function generateEmailOtp() : bool
    {
        $contact = $this->contact->whereEmail(request('email'))->first();

        if ($contact) {
            $this->sendOtp($contact);
            return true;
        }

        $meeting_url = request('ref');
        $meeting_uuid = explode('/', $meeting_url)[3] ?? null;

        if (! $meeting_uuid) {
            throw ValidationException::withMessages(['email' => __('auth.password.user')]);
        }

        $meeting = Meeting::whereUuid($meeting_uuid)->first();

        if ($meeting->getMeta('accessible_via_link') && $meeting->getMeta('status') === MeetingStatus::LIVE) {
            $user = (new User)->forceFill([
                'email' => request('email')
            ]);

            $this->sendOtp($user);
            return true;
        }
    }

    /**
     * Validate Email OTP
     */
    public function validateEmailOtp() : array
    {
        $contact = $this->contact->whereEmail(request('email'))->first();

        if ($contact) {
            $this->validateCache($contact);

            $user = $this->user->forceCreate([
                'uuid'              => Str::uuid(),
                'email'             => request('email'),
                'name'              => $contact->name,
                'email_verified_at' => now(),
                'status'            => UserStatus::ACTIVATED
            ]);

            $user->assignRole('user');

            $user->validateStatus();

            if (request('device_name')) {
                $token = $user->createToken(request('device_name'))->plainTextToken;
            } else {
                \Auth::login($user);
            }

            return [
                'message' => __('auth.login.logged_in'),
                'user'    => new AuthUser($user),
                'token'   => $token ?? null
            ];
        }

        $meeting_url = request('ref');
        $meeting_uuid = explode('/', $meeting_url)[3] ?? null;

        if (! $meeting_uuid) {
            throw ValidationException::withMessages(['email' => __('auth.password.user')]);
        }

        $meeting = Meeting::whereUuid($meeting_uuid)->first();

        if ($meeting && $meeting->getMeta('accessible_via_link') && $meeting->getMeta('status') === MeetingStatus::LIVE) {
            $user = (new User)->forceFill([
                'email' => request('email')
            ]);

            $this->validateCache($user);

            $user = $this->user->forceCreate([
                'uuid'              => Str::uuid(),
                'email'             => request('email'),
                'name'              => ucwords(explode('@', request('email'))[0]),
                'email_verified_at' => now(),
                'status'            => UserStatus::ACTIVATED
            ]);

            $user->assignRole('user');

            $user->validateStatus();

            $contact = $this->contact->firstOrCreate([
                'email'   => request('email')
            ]);

            if (! $contact->user_id) {
                $contact->user_id = $user->id;
                $contact->save();
            }

            $contact->users()->syncWithoutDetaching([$user->id]);

            if (request('device_name')) {
                $token = $user->createToken(request('device_name'))->plainTextToken;
            } else {
                \Auth::login($user);
            }

            $this->invitee->joinWithOtp($meeting_uuid);

            return [
                'message' => __('auth.login.logged_in'),
                'user'    => new AuthUser($user),
                'token'   => $token ?? null
            ];
        }

        throw ValidationException::withMessages(['email' => __('auth.password.user')]);
    }
}
