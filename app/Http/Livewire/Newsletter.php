<?php

namespace App\Http\Livewire;

use App\Models\Site\Subscriber;
use App\Notifications\Subscribed;
use Livewire\Component;

class Newsletter extends Component
{
    public $email = '';
    public $message = '';
    public $error = false;

    public function render()
    {
        return view('livewire.newsletter');
    }

    public function subscribe()
    {
        $this->validate([
            'email' => 'required|email',
        ]);

        if (Subscriber::whereEmail($this->email)->whereNull('unsubscribed_at')->count()) {
            $this->message = 'You are already subscribed!';
            $this->error = true;
            return;
        }

        $subscriber = Subscriber::firstOrNew([
            'email' => $this->email
        ]);

        $subscriber->unsubscribed_at = null;
        $subscriber->save();
        $this->email = '';
        $this->message = 'You are awesome!';
        $this->error = false;

        (new \App\Models\User)->forceFill([
            'email' => $subscriber->email,
        ])->notify(new Subscribed($subscriber));
    }
}
