<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\TaskStatus;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
            return response()->json(Subject::all());
    }

    public function mine(Request $request)
    {
        $user = $request->user();

        $completedStatusId = TaskStatus::where('status', 'completed')->value('id');

        $subjects = Subject::with([
            'tasks' => fn ($query) => $query->where('user_id', $user->id)->with('status'),
        ])
            ->withCount([
                'tasks as my_tasks_count' => fn ($query) => $query->where('user_id', $user->id),
                'tasks as my_pending_tasks_count' => fn ($query) => $query
                    ->where('user_id', $user->id)
                    ->when($completedStatusId, fn ($query) => $query->where('status_id', '<>', $completedStatusId)),
            ])
            ->get();

        return response()->json($subjects);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $data = $request->validate([
            "name" => "required|string"
        ]);

        $subject = Subject::create($data);
        return response()->json($subject, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Subject $subject)
    {
        //
        return $subject;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subject $subject)
    {
        //
        $data = $request->validate([
            "name" => "required|string"
        ]);

        $subject->update($data);
        return response()->json($subject, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subject $subject)
    {
        //
        $subject->delete();
        return response()->json(null, 204);
    }
}
