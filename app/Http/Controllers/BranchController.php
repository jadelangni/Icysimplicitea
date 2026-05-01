<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\CatalogInventorySyncService;

class BranchController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validateWithBag('addBranch', [
            'name' => ['required', 'string', 'max:255', 'unique:branches,name'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'manager_name' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $branch = Branch::create([
            'name' => $validated['name'],
            'address' => $validated['address'],
            'phone' => $validated['phone'] ?? null,
            'manager_name' => $validated['manager_name'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($branch->is_active) {
            app(CatalogInventorySyncService::class)->ensureBranchInventory($branch);
        }

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
        try {
            $branch->update(['is_active' => !$branch->is_active]);

            $message = $branch->is_active ? 'Branch restored successfully.' : 'Branch archived successfully.';

            if ($branch->is_active) {
                app(CatalogInventorySyncService::class)->ensureBranchInventory($branch);
            }

            if (request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            return redirect()->route('employees.index')->with('success', $message);
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to archive/restore branch: ' . $e->getMessage()], 500);
            }
            return redirect()->route('employees.index')->with('error', 'Failed to archive/restore branch.');
        }
    }

    public function destroy(Branch $branch)
    {
        $branchName = $branch->name;

        try {
            DB::beginTransaction();

            // Unassign users from this branch to avoid FK constraint errors
            $branch->users()->update(['branch_id' => null]);

            $branch->delete();

            DB::commit();

            if (request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => "Branch {$branchName} deleted successfully."]);
            }

            return redirect()->route('employees.index')->with('success', "Branch {$branchName} deleted successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to delete branch: ' . $e->getMessage()], 500);
            }
            return redirect()->route('employees.index')->with('error', 'Failed to delete branch.');
        }
    }
}
