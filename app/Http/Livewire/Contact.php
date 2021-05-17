<?php

namespace App\Http\Livewire;

use App\Models\Site\Query;
use Livewire\Component;

class Contact extends Component
{
    public $email = '';
    public $name = '';
    public $contact_number = '';
    public $subject = '';
    public $message = '';
    public $response_message = '';
    public $error = false;

    public function render()
    {
        return view('livewire.contact');
    }

    public function contact()
    {
        $this->validate([
            'email' => 'required|email',
            'name' => 'required|max:100',
            'contact_number' => 'nullable|max:20',
            'subject' => 'required|min:10|max:200',
            'message' => 'required|min:10|max:1000',
        ]);

        $query = Query::forceCreate([
            'email' => $this->email,
            'name' => $this->name,
            'contact_number' => $this->contact_number,
            'subject' => $this->subject,
            'message' => $this->message
        ]);

        $this->email = '';
        $this->name = '';
        $this->subject = '';
        $this->contact_number = '';
        $this->message = '';

        $this->response_message = 'Thank you for contacting us, we will get back to you in next 24 hours!';
        $this->error = false;
    }
}
