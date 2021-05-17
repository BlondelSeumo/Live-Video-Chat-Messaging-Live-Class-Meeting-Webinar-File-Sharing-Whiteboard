<?php

use App\Models\Contact;
use App\Models\Segment;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('update/1.6.0', function() {
    $user = User::role('admin')->first();

    foreach (Segment::all() as $segment) {
        $segment->users()->syncWithoutDetaching([$user->id]);
    }

    foreach (Contact::all() as $contact) {
        $contact->users()->syncWithoutDetaching([$user->id]);
    }

    return 'Version 1.6.0 update completed!';
});