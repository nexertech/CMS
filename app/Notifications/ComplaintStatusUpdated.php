<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Channels\FcmChannel;

class ComplaintStatusUpdated extends Notification
{
    use Queueable;

    protected $complaint;
    protected $status;

    /**
     * Create a new notification instance.
     */
    public function __construct($complaint, $status)
    {
        $this->complaint = $complaint;
        $this->status = $status;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'complaint_id' => $this->complaint->id,
            'status' => $this->status,
            'message' => 'Status changed to ' . ucfirst(str_replace('_', ' ', $this->status)),
            'title' => 'Complaint Updated',
        ];
    }

    /**
     * Get the FCM representation of the notification.
     */
    public function toFcm($notifiable)
    {
        $statusLabel = ucfirst(str_replace('_', ' ', $this->status));
        return [
            'title' => 'Complaint Update: ' . $this->complaint->ticket_number,
            'body' => "Your complaint status has been changed to: {$statusLabel}",
            'sound' => 'default',
            'data' => [
                'complaint_id' => (string)$this->complaint->id,
                'status' => (string)$this->status,
                'type' => 'status_update'
            ]
        ];
    }
}
