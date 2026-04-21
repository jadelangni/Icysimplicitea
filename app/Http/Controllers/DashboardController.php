<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SalesItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\Branch;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            abort(403);
        }
        
        // Admin can select any branch or view all branches, cashier only sees their own branch
        $branches = collect();
        if ($user->isAdmin()) {
            $branches = Branch::where('is_active', true)->get();
            $branchId = $request->get('branch_id', 'all'); // Default to "all" for admin
        } else {
            $branchId = $user->branch_id;
        }
        
        // Determine if viewing all branches
        $isAllBranches = $user->isAdmin() && ($branchId === 'all' || $branchId === null);
        $selectedBranch = $isAllBranches ? null : Branch::find($branchId);

        // Helper function to apply branch filter
        $applyBranchFilter = function($query) use ($isAllBranches, $branchId) {
            if (!$isAllBranches) {
                $query->where('branch_id', $branchId);
            }
            return $query;
        };

        // Get today's sales
        $todaysSales = $applyBranchFilter(Sale::query())
            ->whereDate('created_at', Carbon::today())
            ->sum('total_amount');

        // Get yesterday's sales for comparison
        $yesterdaySales = $applyBranchFilter(Sale::query())
            ->whereDate('created_at', Carbon::yesterday())
            ->sum('total_amount');

        // Calculate percentage change
        $salesChange = $yesterdaySales > 0 
            ? round((($todaysSales - $yesterdaySales) / $yesterdaySales) * 100, 1) 
            : ($todaysSales > 0 ? 100 : 0);

        // Get today's transaction count
        $todaysTransactions = $applyBranchFilter(Sale::query())
            ->whereDate('created_at', Carbon::today())
            ->count();

        // Get yesterday's transactions for comparison
        $yesterdayTransactions = $applyBranchFilter(Sale::query())
            ->whereDate('created_at', Carbon::yesterday())
            ->count();

        $transactionsChange = $yesterdayTransactions > 0 
            ? round((($todaysTransactions - $yesterdayTransactions) / $yesterdayTransactions) * 100, 1) 
            : ($todaysTransactions > 0 ? 100 : 0);

        // Get low stock items count
        $lowStockQuery = Inventory::query()->whereRaw('quantity <= min_stock_level');
        if (!$isAllBranches) {
            $lowStockQuery->where('branch_id', $branchId);
        }
        $lowStockCount = $lowStockQuery->count();

        // Get active products count
        $activeProducts = Product::where('is_active', true)->count();

        // Get this week's total revenue
        $weeklyRevenue = $applyBranchFilter(Sale::query())
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->sum('total_amount');

        // Get last week's revenue for comparison
        $lastWeekRevenue = $applyBranchFilter(Sale::query())
            ->whereBetween('created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()])
            ->sum('total_amount');

        $weeklyChange = $lastWeekRevenue > 0 
            ? round((($weeklyRevenue - $lastWeekRevenue) / $lastWeekRevenue) * 100, 1) 
            : ($weeklyRevenue > 0 ? 100 : 0);

        // Get recent sales with items
        $recentSalesQuery = Sale::with(['user', 'salesItems.product', 'branch'])
            ->orderBy('created_at', 'desc')
            ->limit(5);
        if (!$isAllBranches) {
            $recentSalesQuery->where('branch_id', $branchId);
        }
        $recentSales = $recentSalesQuery->get();

        // Get daily sales for the past 7 days for chart
        $dailySales = [];
        $dailyLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dailyLabels[] = $date->format('M d');
            $dailySales[] = $applyBranchFilter(Sale::query())
                ->whereDate('created_at', $date)
                ->sum('total_amount');
        }

        // Get sales by category for the chart
        $categories = Category::all();
        $categorySales = [];
        foreach ($categories as $category) {
            $totalSales = SalesItem::whereHas('sale', function($q) use ($isAllBranches, $branchId) {
                    if (!$isAllBranches) {
                        $q->where('branch_id', $branchId);
                    }
                    $q->whereDate('created_at', Carbon::today());
                })
                ->whereHas('product', function($q) use ($category) {
                    $q->where('category_id', $category->id);
                })
                ->sum('total_price');
            
            $categorySales[$category->name] = $totalSales;
        }

        // Get top selling products
        $topProducts = SalesItem::select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(total_price) as total_sales'))
            ->whereHas('sale', function($q) use ($isAllBranches, $branchId) {
                if (!$isAllBranches) {
                    $q->where('branch_id', $branchId);
                }
                $q->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            })
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->with('product')
            ->get();

        // Calculate performance score (based on targets)
        $dailyTarget = $isAllBranches ? 15000 : 5000; // Higher target for all branches
        $performanceScore = min(100, round(($todaysSales / max($dailyTarget, 1)) * 100));

        // Use one shared dashboard UI for both admin and employee users
        return view('dashboard', compact(
            'todaysSales',
            'salesChange',
            'todaysTransactions',
            'transactionsChange',
            'lowStockCount',
            'activeProducts',
            'weeklyRevenue',
            'weeklyChange',
            'recentSales',
            'dailySales',
            'dailyLabels',
            'categorySales',
            'topProducts',
            'performanceScore',
            'branches',
            'branchId',
            'selectedBranch'
        ));
    }

    /**
     * Get dashboard data via AJAX for real-time updates
     */
    public function getDashboardData(Request $request)
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        // Admin can select any branch or all branches; non-admins are locked to their branch.
        if ($user->isAdmin()) {
            $branchId = $request->get('branch_id', 'all');
        } else {
            $branchId = $user->branch_id;
        }

        // Handle "all branches" case (admin only)
        $isAllBranches = $user->isAdmin() && ($branchId === 'all' || $branchId === null);
        $selectedBranch = $isAllBranches ? null : Branch::find($branchId);
        $branchName = $isAllBranches ? 'All Branches' : ($selectedBranch->name ?? 'Unknown');

        // Helper function to apply branch filter
        $applyBranchFilter = function($query) use ($isAllBranches, $branchId) {
            if (!$isAllBranches) {
                $query->where('branch_id', $branchId);
            }
            return $query;
        };

        // Get today's sales
        $todaysSales = $applyBranchFilter(Sale::query())
            ->whereDate('created_at', Carbon::today())
            ->sum('total_amount');

        // Get yesterday's sales for comparison
        $yesterdaySales = $applyBranchFilter(Sale::query())
            ->whereDate('created_at', Carbon::yesterday())
            ->sum('total_amount');

        $salesChange = $yesterdaySales > 0 
            ? round((($todaysSales - $yesterdaySales) / $yesterdaySales) * 100, 1) 
            : ($todaysSales > 0 ? 100 : 0);

        // Get today's transaction count
        $todaysTransactions = $applyBranchFilter(Sale::query())
            ->whereDate('created_at', Carbon::today())
            ->count();

        $yesterdayTransactions = $applyBranchFilter(Sale::query())
            ->whereDate('created_at', Carbon::yesterday())
            ->count();

        $transactionsChange = $yesterdayTransactions > 0 
            ? round((($todaysTransactions - $yesterdayTransactions) / $yesterdayTransactions) * 100, 1) 
            : ($todaysTransactions > 0 ? 100 : 0);

        // Get low stock items count
        $lowStockQuery = Inventory::query()->whereRaw('quantity <= min_stock_level');
        if (!$isAllBranches) {
            $lowStockQuery->where('branch_id', $branchId);
        }
        $lowStockCount = $lowStockQuery->count();

        // Get this week's total revenue
        $weeklyRevenue = $applyBranchFilter(Sale::query())
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->sum('total_amount');

        $lastWeekRevenue = $applyBranchFilter(Sale::query())
            ->whereBetween('created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()])
            ->sum('total_amount');

        $weeklyChange = $lastWeekRevenue > 0 
            ? round((($weeklyRevenue - $lastWeekRevenue) / $lastWeekRevenue) * 100, 1) 
            : ($weeklyRevenue > 0 ? 100 : 0);

        // Get recent sales with items
        $recentSalesQuery = Sale::with(['user', 'salesItems.product', 'branch'])
            ->orderBy('created_at', 'desc')
            ->limit(5);
        if (!$isAllBranches) {
            $recentSalesQuery->where('branch_id', $branchId);
        }
        $recentSales = $recentSalesQuery->get();

        // Get daily sales for chart
        $dailySales = [];
        $dailyLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dailyLabels[] = $date->format('M d');
            $dailySales[] = $applyBranchFilter(Sale::query())
                ->whereDate('created_at', $date)
                ->sum('total_amount');
        }

        // Get top selling products
        $topProducts = SalesItem::select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(total_price) as total_sales'))
            ->whereHas('sale', function($q) use ($isAllBranches, $branchId) {
                if (!$isAllBranches) {
                    $q->where('branch_id', $branchId);
                }
                $q->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            })
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->with('product')
            ->get();

        // Calculate performance score
        $dailyTarget = $isAllBranches ? 15000 : 5000; // Higher target for all branches
        $performanceScore = min(100, round(($todaysSales / max($dailyTarget, 1)) * 100));

        return response()->json([
            'branchName' => $branchName,
            'todaysSales' => $todaysSales,
            'salesChange' => $salesChange,
            'todaysTransactions' => $todaysTransactions,
            'transactionsChange' => $transactionsChange,
            'lowStockCount' => $lowStockCount,
            'weeklyRevenue' => $weeklyRevenue,
            'weeklyChange' => $weeklyChange,
            'performanceScore' => $performanceScore,
            'dailySales' => $dailySales,
            'dailyLabels' => $dailyLabels,
            'topProducts' => $topProducts,
            'recentSales' => $recentSales
        ]);
    }

    /**
     * Get cashier dashboard data via AJAX for real-time updates
     */
    public function getCashierDashboardData()
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $branchId = $user->branch_id;

        $todaysSales = Sale::where('branch_id', $branchId)
            ->whereDate('created_at', Carbon::today())
            ->sum('total_amount');

        $yesterdaySales = Sale::where('branch_id', $branchId)
            ->whereDate('created_at', Carbon::yesterday())
            ->sum('total_amount');

        $salesChange = $yesterdaySales > 0 
            ? round((($todaysSales - $yesterdaySales) / $yesterdaySales) * 100, 1) 
            : ($todaysSales > 0 ? 100 : 0);

        $todaysTransactions = Sale::where('branch_id', $branchId)
            ->whereDate('created_at', Carbon::today())
            ->count();

        $yesterdayTransactions = Sale::where('branch_id', $branchId)
            ->whereDate('created_at', Carbon::yesterday())
            ->count();

        $transactionsChange = $yesterdayTransactions > 0 
            ? round((($todaysTransactions - $yesterdayTransactions) / $yesterdayTransactions) * 100, 1) 
            : ($todaysTransactions > 0 ? 100 : 0);

        $weeklyRevenue = Sale::where('branch_id', $branchId)
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->sum('total_amount');

        $lastWeekRevenue = Sale::where('branch_id', $branchId)
            ->whereBetween('created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()])
            ->sum('total_amount');

        $weeklyChange = $lastWeekRevenue > 0 
            ? round((($weeklyRevenue - $lastWeekRevenue) / $lastWeekRevenue) * 100, 1) 
            : ($weeklyRevenue > 0 ? 100 : 0);

        $dailyTarget = 5000;
        $performanceScore = min(100, round(($todaysSales / max($dailyTarget, 1)) * 100));

        $recentSales = Sale::where('branch_id', $branchId)
            ->with(['salesItems.product'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'receipt_number' => $sale->receipt_number,
                    'total_amount' => $sale->total_amount,
                    'payment_method' => $sale->payment_method ?? 'Cash',
                    'items_count' => $sale->salesItems->count(),
                    'created_at' => $sale->created_at->format('M d, Y'),
                    'created_time' => $sale->created_at->format('h:i A'),
                ];
            });

        $topProducts = SalesItem::select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(total_price) as total_sales'))
            ->whereHas('sale', function($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                  ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            })
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->with('product')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->product->name ?? 'Unknown',
                    'total_qty' => $item->total_qty,
                    'total_sales' => $item->total_sales,
                ];
            });

        $dailySales = [];
        $dailyLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dailyLabels[] = $date->format('M d');
            $dailySales[] = Sale::where('branch_id', $branchId)
                ->whereDate('created_at', $date)
                ->sum('total_amount');
        }

        return response()->json([
            'todaysSales' => $todaysSales,
            'salesChange' => $salesChange,
            'todaysTransactions' => $todaysTransactions,
            'transactionsChange' => $transactionsChange,
            'weeklyRevenue' => $weeklyRevenue,
            'weeklyChange' => $weeklyChange,
            'performanceScore' => $performanceScore,
            'recentSales' => $recentSales,
            'topProducts' => $topProducts,
            'dailySales' => $dailySales,
            'dailyLabels' => $dailyLabels,
        ]);
    }

    /**
     * Get recent sales data for AJAX refresh
     */
    public function getRecentSales()
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

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

    /**
     * Get live sales notifications (new sales since last check)
     */
    public function getLiveSales(Request $request)
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $branchId = $request->get('branch_id', $user->branch_id);
        $since = $request->get('since'); // ISO timestamp
        
        // Only admins can view other branches
        if (!$user->isAdmin()) {
            $branchId = $user->branch_id;
        }

        // Build query for new sales
        $query = Sale::with(['user', 'branch', 'salesItems.product'])
            ->orderBy('created_at', 'desc');
        
        // Filter by branch (0 or null = all branches)
        if ($branchId && $branchId != 'all') {
            $query->where('branch_id', $branchId);
        }
        
        // Filter by timestamp if provided
        if ($since) {
            $query->where('created_at', '>', Carbon::parse($since));
        } else {
            // Just get sales from the last minute on first load
            $query->where('created_at', '>', Carbon::now()->subMinute());
        }
        
        $newSales = $query->limit(10)->get();
        
        // Format the response
        $salesData = $newSales->map(function ($sale) {
            return [
                'id' => $sale->id,
                'branch_id' => $sale->branch_id,
                'branch_name' => $sale->branch->name ?? 'Unknown',
                'amount' => $sale->total_amount,
                'formatted_amount' => '₱' . number_format($sale->total_amount, 2),
                'cashier_name' => $sale->user->name ?? 'Unknown',
                'receipt_number' => $sale->receipt_number,
                'items_count' => $sale->items->count(),
                'payment_method' => ucfirst($sale->payment_method),
                'timestamp' => $sale->created_at->toIso8601String(),
                'time_ago' => $sale->created_at->diffForHumans(),
            ];
        });
        
        return response()->json([
            'success' => true,
            'sales' => $salesData,
            'count' => $salesData->count(),
            'server_time' => Carbon::now()->toIso8601String(),
        ]);
    }
}
