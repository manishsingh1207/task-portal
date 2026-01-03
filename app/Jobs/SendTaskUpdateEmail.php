<?php

namespace App\Jobs;

use App\Mail\TaskStatusUpdated;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendTaskUpdateEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Task $task) {}

    public function handle(): void
    {
        $task = $this->task->fresh(['user']);
        if ($task && $task->user && $task->user->email) {
            Mail::to($task->user->email)->send(new TaskStatusUpdated($task));
        }
    }
}
