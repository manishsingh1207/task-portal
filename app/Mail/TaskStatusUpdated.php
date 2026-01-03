<?php

namespace App\Mail;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TaskStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Task $task) {}

    public function build()
    {
        return $this->subject('Task Status Updated')
            ->view('emails.task_status_updated', ['task' => $this->task]);
    }
}
