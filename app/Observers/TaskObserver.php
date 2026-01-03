<?php

namespace App\Observers;

use App\Models\Task;
use App\Models\TaskActivity;
use Illuminate\Support\Facades\Log;

class TaskObserver
{
    public function created(Task $task): void
    {
        TaskActivity::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'action' => 'created',
            'to_status' => $task->status,
        ]);
    }

    public function updated(Task $task)
    {
        if ($task->wasChanged('status')) {
            $from = $task->getOriginal('status');
            $to = $task->status;
            Log::info("Task {$task->id} status changed from {$from} to {$to} by User " . auth()->id());
            TaskActivity::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'action' => 'status_updated',
                'from_status' => $from,
                'to_status' => $to,
            ]);
            // Dispatch queued email if queue configured
            // \App\Jobs\SendTaskUpdateEmail::dispatch($task);
        }
    }

    public function deleted(Task $task): void {}
    public function restored(Task $task): void {}
    public function forceDeleted(Task $task): void {}
}
