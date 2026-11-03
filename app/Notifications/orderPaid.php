<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Queued, because recordPayment() runs inside the Stripe webhook: Stripe retries a
 * delivery it doesn't get a prompt 200 for, and SMTP latency is the slowest thing in
 * that request. Dispatching takes a row insert; the send happens out of band.
 *
 * Trade-off: a send that fails now lands in failed_jobs rather than being caught and
 * report()ed by the caller, so a dead worker loses confirmations silently. Check
 * failed_jobs if customers stop receiving them.
 */
class orderPaid extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('Booking notification.')
                    ->action('See More', url('/'))
                    ->line('Thank you for using Quick Park to reserve your parking space');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
