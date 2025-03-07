<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::all();
        return view('tasks.index', compact('tasks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'task' => 'required|unique:tasks,task'
        ]);

        $task = new Task();
        $task->task = $request->task;
        $task->completed = false;
        $task->save();

        return response()->json(['success' => true, 'task' => $task]);
    }

    public function update($id)
    {
        $task = Task::findOrFail($id);
        $task->completed = !$task->completed;
        $task->save();

        return response()->json(['success' => true, 'completed' => $task->completed]);
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return response()->json(['success' => true]);
    }
}
