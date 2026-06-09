<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

use Illuminate\Notifications\Notification;

class AppointmentCancelledByClient extends Notification
{
    use Queueable;

    protected $appointment;

    /**
     * Create a new notification instance.
     */
    public function __construct($appointment)
    {
        $this->appointment = $appointment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'appointment_id' => $this->appointment->id,
            'client_name' => $this->appointment->client_name,
            'message' => $this->appointment->client_name . ' randevusunu iptal etti.',
            'date' => $this->appointment->appointment_date,
            'time' => $this->appointment->appointment_time,
        ];
    }
}
