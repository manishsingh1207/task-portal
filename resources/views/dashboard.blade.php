<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-900">
                <h3 class="text-lg font-bold">Welcome, {{ auth()->user()->name }}!</h3>
                <p class="mt-2">You are logged in as: <strong>{{ auth()->user()->getRoleNames()->first() }}</strong></p>

                <div class="mt-6">
                    <a href="{{ route('tasks.index') }}" class="bg-indigo-600 text-white px-4 py-2 rounded shadow hover:bg-indigo-700">
                        Go to Task Management Portal →
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>