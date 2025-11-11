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

        return view('dashboard', compact(
            'todaysSales',
            'todaysTransactions', 
            'lowStockCount',
            'activeProducts',
            'recentSales'
        ));
    }
}
