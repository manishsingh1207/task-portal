<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Task Portal</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow sm:rounded-lg">

                @hasanyrole('Admin|Manager')
                <form action="{{ route('tasks.store') }}" method="POST" class="mb-8 p-4 bg-gray-50 rounded">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <input type="text" name="title" placeholder="New Task Title" class="rounded border-gray-300" required>
                        <select name="user_id" class="rounded border-gray-300">
                            @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">Assign Task</button>
                    </div>
                </form>
                @endhasanyrole

                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b">
                            <th class="p-2">Task</th>
                            <th class="p-2">Assigned To</th>
                            <th class="p-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tasks as $task)
                        <tr class="border-b">
                            <td class="p-2">{{ $task->title }}</td>
                            <td class="p-2">{{ $task->user->name }}</td>
                            <td class="p-2">
                                @can('update', $task)
                                <form action="{{ route('tasks.update', $task) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <select name="status" onchange="this.form.submit()" class="text-sm rounded border-gray-200">
                                        <option value="Pending" {{ $task->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="In Progress" {{ $task->status == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="Completed" {{ $task->status == 'Completed' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                </form>
                                @else
                                <span class="text-gray-600 text-sm">{{ $task->status }}</span>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>