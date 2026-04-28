<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    /**
     * Store a newly created branch.
     */
    public function store(Request $request)
    {
        $validated = $request->validateWithBag('addBranch', [
            'name' => ['required', 'string', 'max:255', 'unique:branches,name'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'manager_name' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Branch::create([
            'name' => $validated['name'],
            'address' => $validated['address'],
            'phone' => $validated['phone'] ?? null,
            'manager_name' => $validated['manager_name'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('employees.index')->with('success', 'Branch added successfully.');
    }

    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:branches,name,' . $branch->id],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'manager_name' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $branch->update([
            'name' => $validated['name'],
            'address' => $validated['address'],
            'phone' => $validated['phone'] ?? null,
            'manager_name' => $validated['manager_name'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('employees.index')->with('success', 'Branch updated successfully.');
    }

    public function archive(Branch $branch)
    {
        $branch->update(['is_active' => !$branch->is_active]);

        return redirect()->route('employees.index')->with('success', $branch->is_active ? 'Branch restored successfully.' : 'Branch archived successfully.');
    }

    public function destroy(Branch $branch)
    {
        $branchName = $branch->name;
        $branch->delete();

        return redirect()->route('employees.index')->with('success', "Branch {$branchName} deleted successfully.");
    }
}