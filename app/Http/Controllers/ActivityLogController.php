<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Sale;
use App\Models\SalesItem;
use App\Models\Branch;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    /**
     * Display the cashier list page.
     */
    public function index(Request $request)
    {
        $query = User::with(['branch'])
            ->where('is_active', true)
            ->orderBy('name', 'asc');

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $cashiers = $query->paginate(25)->withQueryString();
        $branches = Branch::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        // Date filter - default to today in PH timezone
        $startDateInput = $request->get('start_date', Carbon::today('Asia/Manila')->format('Y-m-d'));
        $endDateInput = $request->get('end_date', Carbon::today('Asia/Manila')->format('Y-m-d'));
        $startDate = Carbon::parse($startDateInput)->format('M d, Y');
        $endDate = Carbon::parse($endDateInput)->format('M d, Y');
        $filterStartDate = $startDateInput;
        $filterEndDate = $endDateInput;

        return view('activity-logs.index', compact('cashiers', 'branches', 'startDate', 'endDate', 'filterStartDate', 'filterEndDate'));
    }

    /**
     * Show activity details for a specific user (cashier sales details via AJAX).
     */
    public function userActivity(Request $request, User $user)
    {
        $startDateInput = $request->get('start_date', Carbon::today('Asia/Manila')->format('Y-m-d'));
        $endDateInput = $request->get('end_date', Carbon::today('Asia/Manila')->format('Y-m-d'));

        // Convert Manila date to UTC range for proper timezone-aware querying
        // (DB stores timestamps in UTC, but user selects date in Manila timezone)
        $startOfRange = Carbon::parse($startDateInput, 'Asia/Manila')->startOfDay()->utc();
        $endOfRange = Carbon::parse($endDateInput, 'Asia/Manila')->endOfDay()->utc();

        // Get all sales for this user on the specified date range
        $sales = Sale::with(['salesItems.product', 'branch'])
            ->where('user_id', $user->id)
            ->where('status', '!=', 'voided')
            ->whereBetween('created_at', [$startOfRange, $endOfRange])
            ->orderBy('created_at', 'desc')
            ->get();

        // Group items by receipt
        $receipts = [];
        $grandTotal = 0;

        foreach ($sales as $sale) {
            $items = [];
            foreach ($sale->salesItems as $item) {
                $items[] = [
                    'product_name' => $item->product->name ?? 'Unknown Product',
                    'quantity' => $item->quantity,
                    'unit_price' => number_format($item->unit_price, 2),
                    'total_price' => number_format($item->total_price, 2),
                ];
            }

            $receipts[] = [
                'receipt_number' => $sale->receipt_number,
                'time' => $sale->created_at->setTimezone('Asia/Manila')->format('M d, Y h:i A'),
                'subtotal' => number_format($sale->total_amount, 2),
                'items' => $items,
            ];

            $grandTotal += floatval($sale->total_amount);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'name' => $user->name,
                'role' => ucfirst($user->role),
                'branch' => $user->branch->name ?? 'N/A',
            ],
            'date' => Carbon::parse($startDateInput)->format('M d, Y') . ' - ' . Carbon::parse($endDateInput)->format('M d, Y'),
            'receipts' => $receipts,
            'total_sales' => count($sales),
            'grand_total' => number_format($grandTotal, 2),
        ]);
    }
}
