<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\SalesItem;
use App\Models\Ingredient;
use App\Models\IngredientInventory;
use App\Models\Inventory;
use App\Models\Branch;
use App\Models\BranchSession;
use App\Models\Category;
use App\Models\User;
use App\Services\InventoryForecastService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesReportExport;
use App\Exports\InventoryReportExport;
use App\Exports\DailyReportExport;
use App\Exports\MonthlyReportExport;
use App\Exports\RestockPlanExport;

class ReportController extends Controller
{

    /**
     * Get branch filter for queries
     */
    private function getBranchFilter(Request $request)
    {
        /** @var User $user */
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

    private function getInventoryBranchFilter(Request $request): array
    {
        /** @var User $user */
        $user = Auth::user();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        if ($user->isAdmin()) {
            $requestedBranchId = $request->get('branch_id');
            $selectedBranchId = $branches->contains('id', (int) $requestedBranchId)
                ? (int) $requestedBranchId
                : $branches->first()?->id;
        } else {
            $selectedBranchId = $user->branch_id;
        }

        return [
            'branches' => $branches,
            'selectedBranchId' => $selectedBranchId,
            'isAll' => false,
            'canSelectBranch' => $user->isAdmin(),
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
        $branchFilter = $this->getInventoryBranchFilter($request);
        $normalizeStatus = function (string $status): array {
            return match ($status) {
                'No Stock' => ['No Stock', 0],
                'Low Stock' => ['Low Stock', 1],
                default => ['In Stock', 2],
            };
        };

        $branchId = $branchFilter['selectedBranchId'];

        $ingredients = Ingredient::with(['inventories' => function($query) use ($branchId) {
            $query->where('branch_id', $branchId);
        }])->where('is_active', true)->orderBy('name')->get();

        $ingredients->each(function($ingredient) use ($branchId, $normalizeStatus) {
            $inventory = $ingredient->inventories->first();
            $ingredient->branch_quantity = $inventory ? $inventory->quantity : 0;
            $ingredient->branch_unit_cost = $inventory ? ($inventory->unit_cost ?? 0) : 0;
            $rawStatus = $inventory ? $ingredient->getStatusForBranch($branchId) : 'No Stock';
            [$ingredient->branch_status, $ingredient->status_priority] = $normalizeStatus($rawStatus);
            $ingredient->branch_min_stock = $inventory ? $inventory->min_stock_level : 0;
            $ingredient->branch_last_updated = $inventory?->updated_at;
        });

        $ingredients = $ingredients
            ->sortBy(function ($ingredient) {
                return sprintf('%02d-%s', $ingredient->status_priority ?? 9, strtolower($ingredient->name));
            })
            ->values();

        $importIngredients = Ingredient::where('is_active', true)->orderBy('name')->get(['id', 'name', 'unit']);
        
        // Calculate inventory statistics
        $totalItems = $ingredients->count();
        $noStockItems = $ingredients->where('branch_status', 'No Stock')->count();
        $lowStockItems = $ingredients->where('branch_status', 'Low Stock')->count();
        $totalValue = $ingredients->sum(function($ingredient) {
            return $ingredient->branch_quantity * $ingredient->branch_unit_cost;
        });
        
        // Group by status
        $stockStatus = [
            'No Stock' => $noStockItems,
            'Low Stock' => $lowStockItems,
            'In Stock' => $ingredients->where('branch_status', 'In Stock')->count(),
        ];
        
        return view('reports.inventory', array_merge(compact(
            'ingredients',
            'totalItems',
            'noStockItems',
            'lowStockItems', 
            'totalValue',
            'stockStatus'
        ), $branchFilter));
    }

    /**
     * Display predictive inventory forecasting report.
     */
    public function forecast(Request $request, InventoryForecastService $forecastService)
    {
        $branchFilter = $this->getInventoryBranchFilter($request);

        $lookbackDays = max(7, min(90, (int) $request->get('lookback_days', 30)));
        $leadTimeDays = max(1, min(30, (int) $request->get('lead_time_days', 7)));
        $targetCoverDays = max($leadTimeDays, min(60, (int) $request->get('target_cover_days', 14)));

        $forecast = $forecastService->generateForBranch(
            (int) $branchFilter['selectedBranchId'],
            $lookbackDays,
            $leadTimeDays,
            $targetCoverDays
        );

        return view('reports.forecast', array_merge(
            $forecast,
            compact('lookbackDays', 'leadTimeDays', 'targetCoverDays'),
            $branchFilter
        ));
    }


    /**
     * Export Restock Plan to Excel
     */
    public function exportRestock(Request $request, InventoryForecastService $forecastService)
    {
        $branchFilter = $this->getInventoryBranchFilter($request);

        $lookbackDays = max(7, min(90, (int) $request->get('lookback_days', 30)));
        $leadTimeDays = max(1, min(30, (int) $request->get('lead_time_days', 7)));
        $targetCoverDays = max($leadTimeDays, min(60, (int) $request->get('target_cover_days', 14)));

        $export = new RestockPlanExport(
            $forecastService,
            (int) $branchFilter['selectedBranchId'],
            $lookbackDays,
            $leadTimeDays,
            $targetCoverDays
        );

        $fileName = sprintf('restock-plan-branch-%s-%s.xlsx', $branchFilter['selectedBranchId'], now()->format('Ymd'));

        return Excel::download($export, $fileName);
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
        
        return view('reports.monthly', array_merge(compact(
            'totalRevenue',
            'totalTransactions',
            'averageTransaction',
            'dailyBreakdown',
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
        $branchFilter = $this->getInventoryBranchFilter($request);
        $branch = Branch::find($branchFilter['selectedBranchId']);
        $branchName = $branch ? $branch->name : 'All Branches';
        
        $filename = $branchName . ' Inventory Report, ' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new InventoryReportExport($branchFilter['selectedBranchId'], $branchName),
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
