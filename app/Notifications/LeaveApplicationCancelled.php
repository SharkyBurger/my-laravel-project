<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\LeaveApplication;

class LeaveApplicationCancelled extends Notification
{
    use Queueable;

    public $leaveApplication;

    /**
     * Create a new notification instance.
     */
    public function __construct(LeaveApplication $leaveApplication)
    {
        $this->leaveApplication = $leaveApplication;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database']; // You can add 'mail' if configured
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('An approved leave application has been cancelled.')
                    ->line('Employee: ' . $this->leaveApplication->employee->name)
                    ->line('Dates: ' . $this->leaveApplication->start_date->format('M d, Y') . ' - ' . $this->leaveApplication->end_date->format('M d, Y'))
                    ->action('View Applications', url('/admin/leave-applications')) // Adjust URL based on role
                    ->line('Please update your records accordingly.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'leave_application_id' => $this->leaveApplication->id,
            'message' => 'Leave Application Cancelled by ' . $this->leaveApplication->employee->name,
            'employee_id' => $this->leaveApplication->employee_id,
            'type' => 'cancellation'
        ];
    }
}