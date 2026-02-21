<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Auth\Access\Gate;
use Illuminate\Http\Request;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;


class TaskController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        return response()->json(Task::all());
    }

    public function store(Request $request)
    {
        $data = $this->validatedTaskData($request);
        $data['user_id'] = $request->user()->id;

        try {
            $task = Task::create($data);
            return response()->json($task, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function show(Task $task)
    {
        return response()->json($task);
    }

    public function update(Request $request, Task $task)
    {
        $data = $this->validatedTaskData($request);
        $this->authorize("update", $task);

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
        ]);
    }



}
