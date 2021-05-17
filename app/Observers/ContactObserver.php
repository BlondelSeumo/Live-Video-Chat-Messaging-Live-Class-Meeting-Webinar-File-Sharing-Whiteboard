<?php

namespace App\Observers;

use App\Models\Contact;
use App\Models\User;

class ContactObserver
{
    /**
     * Handle the Contact "created" event.
     *
     * @param  \App\Models\Contact  $contact
     * @return void
     */
    public function created(Contact $contact)
    {
        $this->updateUserContact($contact);
    }

    /**
     * Handle the Contact "updated" event.
     *
     * @param  \App\Models\Contact  $contact
     * @return void
     */
    public function updated(Contact $contact)
    {
        $this->updateUserContact($contact);
    }

    /**
     * Handle the Contact "deleted" event.
     *
     * @param  \App\Models\Contact  $contact
     * @return void
     */
    public function deleted(Contact $contact)
    {
        //
    }

    /**
     * Handle the Contact "forceDeleted" event.
     *
     * @param  \App\Models\Contact  $contact
     * @return void
     */
    public function forceDeleted(Contact $contact)
    {
        //
    }

    private function updateUsercontact(Contact $contact)
    {
        $user = User::whereEmail($contact->email)->first();

        if ($user && ! $contact->user_id) {
            $contact->user_id = $user->id;
            $contact->save();
        }
    }
}
