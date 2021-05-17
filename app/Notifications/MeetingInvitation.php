<?php

namespace App\Notifications;

use App\Helpers\CalHelper;
use App\Models\Contact;
use App\Models\Meeting;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MeetingInvitation extends Notification
{
    use Queueable;

    protected $meeting;
    protected $contact;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(
        Meeting $meeting, 
        Contact $contact
    ) {
        $this->meeting = $meeting;
        $this->contact = $contact;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = url('/app/login-email-otp?ref=/live/meetings/' . $this->meeting->uuid);

        return (new MailMessage)
                    ->subject('Meeting Invitation | '.config('app.name'))
                    ->greeting('Hello' . $this->contact->contact_name ? (' '.$this->contact->contact_name) : '')
                    ->line('You have been invited to a meeting!')
                    ->line($this->meeting->title)
                    ->line('Meeting Starts on ' . Carbon::parse($this->meeting->start_date_time)->timezone($notifiable->timezone)->toDateTimeString())
                    ->line('Click on the below link to know more.')
                    ->action('Meeting Details', $url)
                    ->line('If you don\'t want to join, just ignore this invitation.')
                    ->line('Thank you!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
