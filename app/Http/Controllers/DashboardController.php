<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $branchId = $user->branch_id;

        // Get today's sales for the user's branch
        $todaysSales = Sale::where('branch_id', $branchId)
            ->whereDate('created_at', Carbon::today())
            ->sum('total_amount');

        // Get today's transaction count
        $todaysTransactions = Sale::where('branch_id', $branchId)
            ->whereDate('created_at', Carbon::today())
            ->count();

        // Get low stock items count
        $lowStockCount = Inventory::where('branch_id', $branchId)
            ->whereRaw('quantity <= min_stock_level')
            ->count();

        // Get active products count
        $activeProducts = Product::where('is_active', true)->count();

        // Get recent sales
        $recentSales = Sale::where('branch_id', $branchId)
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get daily sales for the past 7 days for chart
        $dailySales = [];
        $dailyLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dailyLabels[] = $date->format('M d');
            $dailySales[] = Sale::where('branch_id', $branchId)
                ->whereDate('created_at', $date)
                ->sum('total_amount');
        }

        return view('dashboard', compact(
            'todaysSales',
            'todaysTransactions', 
            'lowStockCount',
            'activeProducts',
            'recentSales',
            'dailySales',
            'dailyLabels'
        ));
    }

    /**
     * Get recent sales data for AJAX refresh
     */
    public function getRecentSales()
    {
        $user = Auth::user();
        $branchId = $user->branch_id;

        // Get recent sales
        $recentSales = Sale::where('branch_id', $branchId)
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Count today's transactions
        $todaysTransactions = Sale::where('branch_id', $branchId)
            ->whereDate('created_at', Carbon::today())
            ->count();

        return response()->json([
            'recentSales' => $recentSales,
            'todaysTransactions' => $todaysTransactions
        ]);
    }
}
