<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\SalesItem;
use App\Models\Ingredient;
use App\Models\Branch;
use App\Models\BranchSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesReportExport;
use App\Exports\InventoryReportExport;
use App\Exports\DailyReportExport;
use App\Exports\MonthlyReportExport;

class ReportController extends Controller
{
    /**
     * Get branch filter for queries
     */
    private function getBranchFilter(Request $request)
    {
        $user = Auth::user();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        
        // Default to user's branch
        $selectedBranchId = $user->branch_id;
        
        // If admin, allow selecting any branch or all branches
        if ($user->isAdmin()) {
            $selectedBranchId = $request->get('branch_id', 'all');
        }
        
        return [
            'branches' => $branches,
            'selectedBranchId' => $selectedBranchId,
            'isAll' => $selectedBranchId === 'all',
            'canSelectBranch' => $user->isAdmin()
        ];
    }

    /**
     * Display the main reports dashboard
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $branchFilter = $this->getBranchFilter($request);
        
        // Get current month and year for default filtering
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        // Build query based on branch selection
        $salesQuery = Sale::query();
        if (!$branchFilter['isAll']) {
            $salesQuery->where('branch_id', $branchFilter['selectedBranchId']);
        }
        
        // Basic statistics for the reports overview
        $totalSales = (clone $salesQuery)->sum('total_amount');
        $totalTransactions = (clone $salesQuery)->count();
        $averageTransaction = $totalTransactions > 0 ? $totalSales / $totalTransactions : 0;
        
        // Monthly sales for current year
        $monthlySalesQuery = (clone $salesQuery)->whereYear('created_at', $currentYear);
        $monthlySales = $monthlySalesQuery
            ->selectRaw('MONTH(created_at) as month, SUM(total_amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();
        
        // Fill missing months with 0
        $monthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyData[] = $monthlySales[$i] ?? 0;
        }
        
        return view('reports.index', array_merge(compact(
            'totalSales',
            'totalTransactions', 
            'averageTransaction',
            'monthlyData'
        ), $branchFilter));
    }

    /**
     * Display sales reports
     */
    public function sales(Request $request)
    {
        $user = Auth::user();
        $branchFilter = $this->getBranchFilter($request);
        
        // Date filtering - ensure Carbon instances
        $startDate = $request->has('start_date') 
            ? Carbon::parse($request->get('start_date'))->startOfDay()
            : Carbon::now()->startOfMonth();
        $endDate = $request->has('end_date')
            ? Carbon::parse($request->get('end_date'))->endOfDay()
            : Carbon::now()->endOfMonth();
        
        // Build base query
        $salesQuery = Sale::query();
        if (!$branchFilter['isAll']) {
            $salesQuery->where('branch_id', $branchFilter['selectedBranchId']);
        }
        
        // Sales data
        $sales = (clone $salesQuery)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['user', 'branch', 'salesItems.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        // Summary statistics
        $totalRevenue = (clone $salesQuery)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');
        
        $totalTransactions = (clone $salesQuery)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        
        $averageTransaction = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;
        
        // Top selling products
        $topProductsQuery = SalesItem::whereHas('sale', function($query) use ($branchFilter, $startDate, $endDate) {
            if (!$branchFilter['isAll']) {
                $query->where('branch_id', $branchFilter['selectedBranchId']);
            }
            $query->whereBetween('created_at', [$startDate, $endDate]);
        });
        
        $topProducts = $topProductsQuery
            ->select('product_id', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(total_price) as revenue'))
            ->with('product')
            ->groupBy('product_id')
            ->orderBy('total_sold', 'desc')
            ->limit(10)
            ->get();
        
        // Sales by staff/user
        $salesByUserQuery = Sale::query()
            ->whereBetween('created_at', [$startDate, $endDate]);
        if (!$branchFilter['isAll']) {
            $salesByUserQuery->where('branch_id', $branchFilter['selectedBranchId']);
        }
        $salesByUser = $salesByUserQuery
            ->select('user_id', DB::raw('COUNT(*) as transaction_count'), DB::raw('SUM(total_amount) as total_sales'))
            ->with('user')
            ->groupBy('user_id')
            ->orderBy('total_sales', 'desc')
            ->get();
        
        return view('reports.sales', array_merge(compact(
            'sales',
            'totalRevenue',
            'totalTransactions',
            'averageTransaction',
            'topProducts',
            'salesByUser',
            'startDate',
            'endDate'
        ), $branchFilter));
    }

    /**
     * Display inventory reports
     */
    public function inventory(Request $request)
    {
        $branchFilter = $this->getBranchFilter($request);
        
        // If viewing all branches, get all inventory data
        if ($branchFilter['isAll']) {
            $branches = Branch::all();
            $ingredients = Ingredient::with('inventories')->where('is_active', true)->orderBy('name')->get();
            
            // Add aggregated data for all branches
            $ingredients->each(function($ingredient) use ($branches) {
                $totalQuantity = $ingredient->inventories->sum('quantity');
                $ingredient->branch_quantity = $totalQuantity;
                $ingredient->branch_unit_cost = $ingredient->inventories->avg('unit_cost') ?? 0;
                
                // Check status across all branches
                $hasLowStock = $ingredient->inventories->contains(function($inv) use ($ingredient) {
                    return $inv->quantity > 0 && $inv->quantity <= ($inv->min_stock_level ?? $ingredient->min_stock_level);
                });
                $hasOutOfStock = $ingredient->inventories->contains(function($inv) {
                    return $inv->quantity <= 0;
                });
                
                if ($hasOutOfStock) {
                    $ingredient->branch_status = 'Out of Stock';
                } elseif ($hasLowStock) {
                    $ingredient->branch_status = 'Low Stock';
                } else {
                    $ingredient->branch_status = 'In Stock';
                }
            });
        } else {
            $branchId = $branchFilter['selectedBranchId'];
            
            // Get all ingredients with their inventory data for this branch
            $ingredients = Ingredient::with(['inventories' => function($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            }])->where('is_active', true)->orderBy('name')->get();
            
            // Add branch-specific data to each ingredient
            $ingredients->each(function($ingredient) use ($branchId) {
                $inventory = $ingredient->inventories->first();
                $ingredient->branch_quantity = $inventory ? $inventory->quantity : 0;
                $ingredient->branch_unit_cost = $inventory ? ($inventory->unit_cost ?? 0) : 0;
                $ingredient->branch_status = $ingredient->getStatusForBranch($branchId);
                $ingredient->branch_min_stock = $inventory ? $inventory->min_stock_level : 0;
            });
        }
        
        // Calculate inventory statistics
        $totalItems = $ingredients->count();
        $lowStockItems = $ingredients->where('branch_status', 'Low Stock')->count();
        $outOfStockItems = $ingredients->where('branch_status', 'Out of Stock')->count();
        $totalValue = $ingredients->sum(function($ingredient) {
            return $ingredient->branch_quantity * $ingredient->branch_unit_cost;
        });
        
        // Group by status
        $stockStatus = [
            'In Stock' => $ingredients->where('branch_status', 'In Stock')->count(),
            'Low Stock' => $lowStockItems,
            'Out of Stock' => $outOfStockItems
        ];
        
        return view('reports.inventory', array_merge(compact(
            'ingredients',
            'totalItems',
            'lowStockItems', 
            'outOfStockItems',
            'totalValue',
            'stockStatus'
        ), $branchFilter));
    }

    /**
     * Display daily reports
     */
    public function daily(Request $request)
    {
        $branchFilter = $this->getBranchFilter($request);
        
        $selectedDate = $request->get('date', Carbon::today());
        $date = Carbon::parse($selectedDate);
        
        // Daily sales summary
        $dailySalesQuery = Sale::whereDate('created_at', $date)
            ->with(['user', 'salesItems.product', 'branch'])
            ->orderBy('created_at', 'desc');
        
        if (!$branchFilter['isAll']) {
            $dailySalesQuery->where('branch_id', $branchFilter['selectedBranchId']);
        }
        
        $dailySales = $dailySalesQuery->get();
        
        $totalRevenue = $dailySales->sum('total_amount');
        $totalTransactions = $dailySales->count();
        $averageTransaction = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;
        
        // Payment method breakdown
        $paymentMethods = $dailySales->groupBy('payment_method')
            ->map(function($sales) {
                return [
                    'count' => $sales->count(),
                    'total' => $sales->sum('total_amount')
                ];
            });
        
        // Hourly sales distribution
        $hourlySales = [];
        for ($hour = 6; $hour <= 22; $hour++) {
            $hourlyRevenue = $dailySales->filter(function($sale) use ($hour) {
                return $sale->created_at->hour == $hour;
            })->sum('total_amount');
            
            $hourlySales[] = [
                'hour' => $hour . ':00',
                'revenue' => $hourlyRevenue
            ];
        }
        
        return view('reports.daily', array_merge(compact(
            'dailySales',
            'totalRevenue',
            'totalTransactions',
            'averageTransaction',
            'paymentMethods',
            'hourlySales',
            'selectedDate'
        ), $branchFilter));
    }

    /**
     * Display monthly reports
     */
    public function monthly(Request $request)
    {
        $branchFilter = $this->getBranchFilter($request);
        
        $selectedMonth = (int) $request->get('month', Carbon::now()->month);
        $selectedYear = (int) $request->get('year', Carbon::now()->year);
        
        // Monthly sales data
        $startOfMonth = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->startOfMonth();
        $endOfMonth = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->endOfMonth();
        
        $monthlySalesQuery = Sale::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->with(['salesItems.product', 'branch']);
        
        if (!$branchFilter['isAll']) {
            $monthlySalesQuery->where('branch_id', $branchFilter['selectedBranchId']);
        }
        
        $monthlySales = $monthlySalesQuery->get();
        
        $totalRevenue = $monthlySales->sum('total_amount');
        $totalTransactions = $monthlySales->count();
        $averageTransaction = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;
        
        // Daily breakdown for the month
        $dailyBreakdown = [];
        $currentDate = $startOfMonth->copy();
        
        while ($currentDate <= $endOfMonth) {
            $dayRevenue = $monthlySales->filter(function($sale) use ($currentDate) {
                return $sale->created_at->toDateString() == $currentDate->toDateString();
            })->sum('total_amount');
            
            $dailyBreakdown[] = [
                'date' => $currentDate->format('M d'),
                'day' => $currentDate->format('D'),
                'revenue' => $dayRevenue
            ];
            
            $currentDate->addDay();
        }
        
        // Product performance for the month
        $productPerformanceQuery = SalesItem::whereHas('sale', function($query) use ($branchFilter, $startOfMonth, $endOfMonth) {
                $query->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
                if (!$branchFilter['isAll']) {
                    $query->where('branch_id', $branchFilter['selectedBranchId']);
                }
            })
            ->select('product_id', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(total_price) as revenue'))
            ->with('product')
            ->groupBy('product_id')
            ->orderBy('revenue', 'desc');
        
        $productPerformance = $productPerformanceQuery->get();
        
        return view('reports.monthly', array_merge(compact(
            'totalRevenue',
            'totalTransactions',
            'averageTransaction',
            'dailyBreakdown',
            'productPerformance',
            'selectedMonth',
            'selectedYear'
        ), $branchFilter));
    }

    /**
     * Export sales report to Excel
     */
    public function exportSales(Request $request)
    {
        $branchFilter = $this->getBranchFilter($request);
        
        $startDate = $request->has('start_date') 
            ? Carbon::parse($request->get('start_date'))->startOfDay()
            : Carbon::now()->startOfMonth();
        $endDate = $request->has('end_date')
            ? Carbon::parse($request->get('end_date'))->endOfDay()
            : Carbon::now()->endOfMonth();

        $filename = 'sales_report_' . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new SalesReportExport($startDate, $endDate, $branchFilter['selectedBranchId']),
            $filename
        );
    }

    /**
     * Export inventory report to Excel
     */
    public function exportInventory(Request $request)
    {
        $branchFilter = $this->getBranchFilter($request);
        
        $filename = 'inventory_report_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(
            new InventoryReportExport($branchFilter['selectedBranchId']),
            $filename
        );
    }

    /**
     * Export daily report to Excel
     */
    public function exportDaily(Request $request)
    {
        $branchFilter = $this->getBranchFilter($request);
        
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $parsedDate = Carbon::parse($date);
        
        $filename = 'daily_report_' . $parsedDate->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new DailyReportExport($parsedDate, $branchFilter['selectedBranchId']),
            $filename
        );
    }

    /**
     * Export monthly report to Excel
     */
    public function exportMonthly(Request $request)
    {
        $branchFilter = $this->getBranchFilter($request);
        
        $month = (int) $request->get('month', Carbon::now()->month);
        $year = (int) $request->get('year', Carbon::now()->year);
        
        $filename = 'monthly_report_' . $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '.xlsx';

        return Excel::download(
            new MonthlyReportExport($month, $year, $branchFilter['selectedBranchId']),
            $filename
        );
    }

    /**
     * Print daily report as receipt (thermal printer format)
     */
    public function printDailyReceipt(Request $request)
    {
        $user = Auth::user();
        $date = Carbon::parse($request->get('date', Carbon::today()));
        $redirectAfterPrint = $request->get('redirect_after_print', false);
        
        // For cashiers, always filter by their branch and their own sales
        $branchId = $user->branch_id;
        $branch = Branch::find($branchId);
        
        // Get daily sales for this cashier
        $dailySalesQuery = Sale::whereDate('created_at', $date)
            ->where('branch_id', $branchId)
            ->where('user_id', $user->id)
            ->with(['salesItems.product'])
            ->orderBy('created_at', 'desc');
        
        $dailySales = $dailySalesQuery->get();
        
        $totalRevenue = $dailySales->sum('total_amount');
        $totalTransactions = $dailySales->count();
        $averageTransaction = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;
        
        // Payment method breakdown
        $paymentMethods = $dailySales->groupBy('payment_method')
            ->map(function($sales) {
                return [
                    'count' => $sales->count(),
                    'total' => $sales->sum('total_amount')
                ];
            });
        
        return view('reports.daily-receipt-print', compact(
            'dailySales',
            'totalRevenue',
            'totalTransactions',
            'averageTransaction',
            'paymentMethods',
            'date',
            'branch',
            'redirectAfterPrint'
        ))->with('cashier', $user);
    }

    /**
     * Cashier pre-logout daily report print page.
     * Shows all branch sales for the day (not just this cashier's) and lists all duty staff.
     */
    public function cashierLogoutReport(Request $request)
    {
        $user = Auth::user();
        $date = Carbon::today();
        $branchId = $user->branch_id;
        $branch = Branch::find($branchId);
        
        // Get ALL branch sales for today (all employees at this branch)
        $dailySales = Sale::whereDate('created_at', $date)
            ->where('branch_id', $branchId)
            ->with(['salesItems.product', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        $totalRevenue = $dailySales->sum('total_amount');
        $totalTransactions = $dailySales->count();
        $averageTransaction = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;
        
        // Payment method breakdown
        $paymentMethods = $dailySales->groupBy('payment_method')
            ->map(function($sales) {
                return [
                    'count' => $sales->count(),
                    'total' => $sales->sum('total_amount')
                ];
            });

        // Get today's duty staff from branch sessions
        $todayDutyStaff = BranchSession::getTodayDutyStaff($branchId);
        
        $redirectAfterPrint = true;
        
        return view('reports.daily-receipt-print', compact(
            'dailySales',
            'totalRevenue',
            'totalTransactions',
            'averageTransaction',
            'paymentMethods',
            'date',
            'branch',
            'redirectAfterPrint',
            'todayDutyStaff'
        ))->with('cashier', $user);
    }
}
