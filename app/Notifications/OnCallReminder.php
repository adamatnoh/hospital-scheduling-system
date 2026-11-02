<?php

namespace App\Notifications;

use App\Models\ScheduleAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OnCallReminder extends Notification
{
    use Queueable;

    public function __construct(public ScheduleAssignment $assignment) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('HSS | You are on call tomorrow')
            ->greeting('Hello Dr '.$notifiable->name)
            ->line('You are on call on '.$this->assignment->date->toFormattedDateString().' for '.$this->assignment->department->name.'.')
            ->action('Your schedule', url('/your-schedule'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'assignment_id' => $this->assignment->id,
            'date' => $this->assignment->date->toDateString(),
            'department' => $this->assignment->department->name,
        ];
    }
}
