<?php

namespace App\Repositories\Auth;

use App\Enums\Auth\UserStatus;
use App\Models\Contact;
use App\Models\User;

class SocialLoginRepository
{
    protected $user;
    protected $contact;

    /**
     * Instantiate a new instance
     * @return void
     */
    public function __construct(
        User $user,
        Contact $contact
    ) {
        $this->user = $user;
        $this->contact = $contact;
    }

    /**
     * Handle social login
     *
     * @param String $provider
     * @param Object $provider_user
     */
    public function handle($provider, $provider_user) : void
    {
        $name = $provider_user->getName();
        $email = $provider_user->getEmail();

        $user = $this->user->whereEmail($email)->first();

        if (! $user) {
            $user = $this->user->forceCreate([
                'name' => $name,
                'email' => $email
            ]);

            $user->status = UserStatus::ACTIVATED;
            $user->meta = ['social_login_provider' => $provider];
            $user->save();

            $user->assignRole('user');

            $contact = $this->contact->whereEmail($user->email)->first();

            if ($contact && $contact->user_id != $user->id) {
                $contact->user_id = $user->id;
                $contact->save();
            }

            if (! $contact) {
                $contact = $this->contact->forceCreate([
                    'email' => $email,
                    'user_id' => $user->id
                ]);

                $contact->users()->syncWithoutDetaching([\Auth::id() => ['name' => $name]]);
            }
        }

        $user->validateStatus();

        \Auth::login($user);
    }
}