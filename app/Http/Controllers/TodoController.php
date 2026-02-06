<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TodoController extends Controller
{
    /**
     * Display a listing of todos.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Todo::with(['creator', 'assignee', 'branch']);

        // Admins see all todos, regular users see only their assigned or created todos
        if (!$user->isAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('assigned_to', $user->id);
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority !== 'all') {
            $query->where('priority', $request->priority);
        }

        // Filter by assignee
        if ($request->has('assignee') && $request->assignee !== 'all') {
            $query->where('assigned_to', $request->assignee);
        }

        // Sort
        $sortBy = $request->get('sort', 'due_date');
        $sortOrder = $request->get('order', 'asc');
        
        if ($sortBy === 'due_date') {
            $query->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
                  ->orderBy('due_date', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $todos = $query->paginate(15);
        $users = $user->isAdmin() ? User::all() : collect([$user]);
        $branches = Branch::all();

        // Stats
        $stats = [
            'total' => Todo::when(!$user->isAdmin(), fn($q) => $q->where('user_id', $user->id)->orWhere('assigned_to', $user->id))->count(),
            'pending' => Todo::when(!$user->isAdmin(), fn($q) => $q->where('user_id', $user->id)->orWhere('assigned_to', $user->id))->pending()->count(),
            'in_progress' => Todo::when(!$user->isAdmin(), fn($q) => $q->where('user_id', $user->id)->orWhere('assigned_to', $user->id))->inProgress()->count(),
            'completed' => Todo::when(!$user->isAdmin(), fn($q) => $q->where('user_id', $user->id)->orWhere('assigned_to', $user->id))->completed()->count(),
            'overdue' => Todo::when(!$user->isAdmin(), fn($q) => $q->where('user_id', $user->id)->orWhere('assigned_to', $user->id))->overdue()->count(),
        ];

        return view('todos.index', compact('todos', 'users', 'branches', 'stats'));
    }

    /**
     * Show the form for creating a new todo.
     */
    public function create()
    {
        $user = Auth::user();
        $users = $user->isAdmin() ? User::where('is_active', true)->get() : collect([$user]);
        $branches = Branch::all();

        return view('todos.create', compact('users', 'branches'));
    }

    /**
     * Store a newly created todo.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'branch_id' => 'nullable|exists:branches,id',
            'due_date' => 'nullable|date',
        ]);

        $todo = Todo::create([
            'user_id' => Auth::id(),
            'assigned_to' => $request->assigned_to,
            'branch_id' => $request->branch_id,
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
            'status' => Todo::STATUS_PENDING,
        ]);

        return redirect()->route('todos.index')
            ->with('success', 'Task created successfully!');
    }

    /**
     * Display the specified todo.
     */
    public function show(Todo $todo)
    {
        $this->authorize('view', $todo);
        
        $todo->load(['creator', 'assignee', 'branch']);
        
        return view('todos.show', compact('todo'));
    }

    /**
     * Show the form for editing the specified todo.
     */
    public function edit(Todo $todo)
    {
        $this->authorize('update', $todo);

        $user = Auth::user();
        $users = $user->isAdmin() ? User::where('is_active', true)->get() : collect([$user]);
        $branches = Branch::all();

        return view('todos.edit', compact('todo', 'users', 'branches'));
    }

    /**
     * Update the specified todo.
     */
    public function update(Request $request, Todo $todo)
    {
        $this->authorize('update', $todo);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'assigned_to' => 'nullable|exists:users,id',
            'branch_id' => 'nullable|exists:branches,id',
            'due_date' => 'nullable|date',
        ]);

        $updateData = $request->only([
            'title', 'description', 'priority', 'status', 
            'assigned_to', 'branch_id', 'due_date'
        ]);

        // Set completed_at if status is being changed to completed
        if ($request->status === Todo::STATUS_COMPLETED && $todo->status !== Todo::STATUS_COMPLETED) {
            $updateData['completed_at'] = now();
        } elseif ($request->status !== Todo::STATUS_COMPLETED) {
            $updateData['completed_at'] = null;
        }

        $todo->update($updateData);

        return redirect()->route('todos.index')
            ->with('success', 'Task updated successfully!');
    }

    /**
     * Remove the specified todo.
     */
    public function destroy(Todo $todo)
    {
        $this->authorize('delete', $todo);

        $todo->delete();

        return redirect()->route('todos.index')
            ->with('success', 'Task deleted successfully!');
    }

    /**
     * Toggle todo completion status via AJAX.
     */
    public function toggleComplete(Todo $todo)
    {
        $this->authorize('update', $todo);

        if ($todo->isCompleted()) {
            $todo->update([
                'status' => Todo::STATUS_PENDING,
                'completed_at' => null,
            ]);
            $message = 'Task marked as pending.';
        } else {
            $todo->markAsCompleted();
            $message = 'Task marked as complete!';
        }

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'todo' => $todo->fresh(),
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Update todo status via AJAX.
     */
    public function updateStatus(Request $request, Todo $todo)
    {
        $this->authorize('update', $todo);

        $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled',
        ]);

        $updateData = ['status' => $request->status];

        if ($request->status === Todo::STATUS_COMPLETED) {
            $updateData['completed_at'] = now();
        } else {
            $updateData['completed_at'] = null;
        }

        $todo->update($updateData);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully!',
                'todo' => $todo->fresh(),
            ]);
        }

        return back()->with('success', 'Status updated successfully!');
    }
}
