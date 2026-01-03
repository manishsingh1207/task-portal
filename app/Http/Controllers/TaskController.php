<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $query = Task::with('user');
        if ($user->hasRole('Staff')) {
            $query->where('user_id', $user->id);
        }
        $tasks = $query->get();
        $users = $user->hasAnyRole(['Admin', 'Manager']) ? User::select('id', 'name')->orderBy('name')->get() : collect();
        return view('tasks.index', compact('tasks', 'users'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Task::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
        ]);

        Task::create([
            'title' => $validated['title'],
            'user_id' => $validated['user_id'],
            'creator_id' => auth()->id(),
            'status' => 'Pending',
        ]);

        return back()->with('success', 'Task assigned successfully!');
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['Pending', 'In Progress', 'Completed'])],
        ]);

        $task->update(['status' => $validated['status']]);
        return back()->with('success', 'Status updated!');
    }

    public function apiIndex()
    {
        $user = auth()->user();
        $query = Task::with('user');
        if ($user->hasRole('Staff')) {
            $query->where('user_id', $user->id);
        }
        return $query->get();
    }

    public function apiStore(Request $r)
    {
        $this->authorize('create', Task::class);
        $validated = $r->validate([
            'title' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
        ]);
        return Task::create([
            'title' => $validated['title'],
            'user_id' => $validated['user_id'],
            'creator_id' => auth()->id(),
            'status' => 'Pending',
        ]);
    }
}
