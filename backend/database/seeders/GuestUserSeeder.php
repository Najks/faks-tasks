<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Subject;
use App\Models\Task;
use App\Models\TaskStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GuestUserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or find the guest user
        $user = User::firstOrCreate(
            ['email' => 'guest@example.com'],
            [
                'name' => 'Guest User',
                'username' => 'guest',
                'role' => 'guest',
                'password' => Hash::make('guestpassword'),
            ]
        );

        // Load subjects; if none exist, exit gracefully
        $subjects = Subject::all();
        if ($subjects->isEmpty()) {
            return;
        }

        // Resolve preferred statuses, falling back to any available status
        $notStarted = TaskStatus::where('status', 'not_started')->first() ?? TaskStatus::first();
        $completed = TaskStatus::where('status', 'completed')->first() ?? TaskStatus::first();

        foreach ($subjects as $subject) {
            // Pending demo task
            $pendingTitle = 'Demo: ' . $subject->name . ' - Practice';
            Task::firstOrCreate(
                [
                    'title' => $pendingTitle,
                    'user_id' => $user->id,
                    'subject_id' => $subject->id,
                ],
                [
                    'description' => 'Demo pending task for ' . $subject->name . '.',
                    'due_date' => now()->addDays(7)->toDateString(),
                    'status_id' => $notStarted->id,
                ]
            );

            // Completed demo task
            $completedTitle = 'Demo: ' . $subject->name . ' - Completed Exercise';
            Task::firstOrCreate(
                [
                    'title' => $completedTitle,
                    'user_id' => $user->id,
                    'subject_id' => $subject->id,
                ],
                [
                    'description' => 'Demo completed task for ' . $subject->name . '.',
                    'due_date' => now()->subDays(3)->toDateString(),
                    'status_id' => $completed->id,
                    'grade' => 9.0,
                ]
            );
        }
    }
}

