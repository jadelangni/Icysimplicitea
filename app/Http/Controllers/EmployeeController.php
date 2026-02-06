<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees.
     */
    public function index(Request $request)
    {
        $query = User::with('branch');
        
        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        
        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        
        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $employees = $query->orderBy('name')->paginate(10)->withQueryString();
        $branches = Branch::where('is_active', true)->get();
        
        return view('employees.index', compact('employees', 'branches'));
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create()
    {
        $branches = Branch::where('is_active', true)->get();
        return view('employees.create', compact('branches'));
    }

    /**
     * Store a newly created employee.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:admin,cashier'],
            'branch_id' => ['required', 'exists:branches,id'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'branch_id' => $validated['branch_id'],
            'is_active' => true,
        ]);

        return redirect()->route('employees.index')
            ->with('success', 'Employee created successfully!');
    }

    /**
     * Show the form for editing an employee.
     */
    public function edit(User $employee)
    {
        $branches = Branch::where('is_active', true)->get();
        return view('employees.edit', compact('employee', 'branches'));
    }

    /**
     * Update the specified employee.
     */
    public function update(Request $request, User $employee)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $employee->id],
            'role' => ['required', 'in:admin,cashier'],
            'branch_id' => ['required', 'exists:branches,id'],
            'is_active' => ['boolean'],
        ]);

        $employee->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'branch_id' => $validated['branch_id'],
            'is_active' => $request->boolean('is_active'),
        ]);

        // Update password if provided
        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Rules\Password::defaults()],
            ]);
            $employee->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('employees.index')
            ->with('success', 'Employee updated successfully!');
    }

    /**
     * Quick update branch assignment via AJAX.
     */
    public function updateBranch(Request $request, User $employee)
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
        ]);

        $employee->update(['branch_id' => $validated['branch_id']]);
        
        $branch = Branch::find($validated['branch_id']);

        return response()->json([
            'success' => true,
            'message' => "Employee assigned to {$branch->name}",
            'branch_name' => $branch->name
        ]);
    }

    /**
     * Toggle employee active status.
     */
    public function toggleStatus(User $employee)
    {
        $employee->update(['is_active' => !$employee->is_active]);
        
        $status = $employee->is_active ? 'activated' : 'deactivated';
        
        return response()->json([
            'success' => true,
            'message' => "Employee {$status} successfully",
            'is_active' => $employee->is_active
        ]);
    }

    /**
     * Remove the specified employee.
     */
    public function destroy(User $employee)
    {
        // Prevent deleting self
        if ($employee->id === auth()->id()) {
            return redirect()->route('employees.index')
                ->with('error', 'You cannot delete your own account!');
        }

        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Employee deleted successfully!');
    }
}
