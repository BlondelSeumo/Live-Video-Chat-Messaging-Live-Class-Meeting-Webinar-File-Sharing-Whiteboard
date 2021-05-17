<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class DemoNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('Hello from ' . config('app.name'))
            ->badge(url('/images/icon-32.png'))
            ->icon(url('/images/icon-192.png'))
            // ->image(url('/images/logo.png'))
            ->body('Thank you for using our application.')
            // ->action('Join Meeting', 'join_meeting')
            ->data([
                'id' => $notification->id,
                'url' => url('/')
                // 'urls' => array(
                //  'join_meeting' => url('/app/live/meetings/ee0d4ef5-c0ac-4b21-ae1d-024478d26f0f')
                // )
            ]);
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
            'title' => 'Hello from ' . config('app.name'),
            'body' => 'Thank you for using our application.',
            'action_url' => url('/'),
            'created' => Carbon::now()->toIso8601String()
        ];
    }
}
