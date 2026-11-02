<?php

namespace App\Notifications;

use App\Models\ScheduleRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestDecided extends Notification
{
    use Queueable;

    public function __construct(public ScheduleRequest $request) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('HSS | '.$this->request->type->label().' request '.$this->request->status->label())
            ->greeting('Hello Dr '.$notifiable->name)
            ->line('Your '.$this->request->type->label().' request for '.$this->request->start_date->toFormattedDateString().' was '.$this->request->status->label().'.');

        if ($this->request->rejection_reason) {
            $mail->line('Reason: '.$this->request->rejection_reason);
        }

        return $mail->action('View history', url('/history'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'request_id' => $this->request->id,
            'type' => $this->request->type->value,
            'status' => $this->request->status->value,
            'rejection_reason' => $this->request->rejection_reason,
            'start_date' => $this->request->start_date->toDateString(),
        ];
    }
}
