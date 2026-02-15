<?php

namespace App\Http\Controllers;

use App\Models\TaskStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TaskStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return response()->json(TaskStatus::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $data = $request->validate([
            "status" => "required|string"
        ]);

        $status = TaskStatus::create($data);
        return response()->json($status, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(TaskStatus $taskStatus)
    {
        //
        return $taskStatus;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TaskStatus $taskStatus)    {
        //
        $data = $request->validate([
            "status" => "required|string"
        ]);

        $taskStatus->update($data);
        \Log::info('Update called', ['id' => $taskStatus->id, 'data' => $request->all()]);
        return response()->json($taskStatus, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TaskStatus $taskStatus)
    {
        //
        $taskStatus->delete();
        return response()->noContent();
    }
}
