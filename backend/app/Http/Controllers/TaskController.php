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
        $data = $this->validatedTaskStoreData($request);
        $data['user_id'] = $request->user()->id;

        try {
            $task = Task::create($data);
            return response()->json($task->load('status'), 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function show(Task $task)
    {
        return response()->json($task->load(['status', 'subject']));
    }

    public function update(Request $request, Task $task)
    {
        $data = $this->validatedTaskData($request);
        $this->authorize("update", $task);

        $task->update($data);

        return response()->json($task->load(['status', 'subject']));
    }

    public function updateStatus(Request $request, Task $task)
    {
        $this->authorize("update", $task);
        $data = $this->validatedTaskStatusData($request);

        $task->update(['status_id' => $data['status_id']]);

        return response()->json($task->load('status'));
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return response()->noContent();
    }

    public function allTasksFromUser(int $userId)
    {
        try {
            $tasks = Task::with('subject')->where('user_id', $userId)->get();
        } catch (\Exception $exception){
            return response()->json(["message" => "error while getting user"]);
        }
        return response()->json($tasks);
    }

    private function validatedTaskData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'due_date' => ['required', 'date'],
            'grade' => ['nullable', 'numeric'],
            'status_id' => ['required', 'int'],
            'subject_id' => ['required', 'int'],
        ]);
    }

    private function validatedTaskStoreData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'due_date' => ['required', 'date'],
            'grade' => ['nullable', 'numeric'],
            'status_id' => ['required', 'int'],
            'subject_id' => ['required', 'int'],
        ]);
    }

    private function validatedTaskStatusData(Request $request): array
    {
        return $request->validate([
            'status_id' => ['required', 'int'],
        ]);
    }
}
