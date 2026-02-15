<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index()
    {
        return response()->json(Task::all());
    }

    public function store(Request $request)
    {
        $data = $this->validatedTaskData($request);

        $task = Task::create($data);

        return response()->json($task, 201);
    }

    public function show(Task $task)
    {
        return response()->json($task);
    }

    public function update(Request $request, Task $task)
    {
        $data = $this->validatedTaskData($request);

        $task->update($data);

        return response()->json($task);
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return response()->noContent();
    }

    public function allTasksFromUser(int $userId)
    {
        $tasks = Task::where('user_id', $userId)->get();
        return response()->json($tasks);
    }

    private function validatedTaskData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'due_date' => ['required', 'date'],
            'grade' => ['required', 'string'],
            'status_id' => ['required', 'int'],
            'subject_id' => ['required', 'int'],
            'user_id' => ['required', 'int'],
        ]);
    }



}
