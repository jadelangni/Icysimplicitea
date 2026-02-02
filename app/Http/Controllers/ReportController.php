<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\SalesItem;
use App\Models\Ingredient;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display the main reports dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $branchId = $user->branch_id;
        
        // Get current month and year for default filtering
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        // Basic statistics for the reports overview
        $totalSales = Sale::where('branch_id', $branchId)->sum('total_amount');
        $totalTransactions = Sale::where('branch_id', $branchId)->count();
        $averageTransaction = $totalTransactions > 0 ? $totalSales / $totalTransactions : 0;
        
        // Monthly sales for current year
        $monthlySales = Sale::where('branch_id', $branchId)
            ->whereYear('created_at', $currentYear)
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
        
        return view('reports.index', compact(
            'totalSales',
            'totalTransactions', 
            'averageTransaction',
            'monthlyData'
        ));
    }

    /**
     * Display sales reports
     */
    public function sales(Request $request)
    {
        $user = Auth::user();
        $branchId = $user->branch_id;
        
        // Date filtering
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth());
        
        // Sales data
        $sales = Sale::where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['user', 'salesItems.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        // Summary statistics
        $totalRevenue = Sale::where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');
        
        $totalTransactions = Sale::where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        
        $averageTransaction = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;
        
        // Top selling products
        $topProducts = SalesItem::whereHas('sale', function($query) use ($branchId, $startDate, $endDate) {
                $query->where('branch_id', $branchId)
                      ->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->select('product_id', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(subtotal) as revenue'))
            ->with('product')
            ->groupBy('product_id')
            ->orderBy('total_sold', 'desc')
            ->limit(10)
            ->get();
        
        return view('reports.sales', compact(
            'sales',
            'totalRevenue',
            'totalTransactions',
            'averageTransaction',
            'topProducts',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Display inventory reports
     */
    public function inventory()
    {
        $user = Auth::user();
        $branchId = $user->branch_id;
        
        // Get all ingredients for this branch
        $ingredients = Ingredient::where('branch_id', $branchId)
            ->orderBy('name')
            ->get();
        
        // Calculate inventory statistics
        $totalItems = $ingredients->count();
        $lowStockItems = $ingredients->where('status', 'Low Stock')->count();
        $outOfStockItems = $ingredients->where('status', 'Out of Stock')->count();
        $totalValue = $ingredients->sum(function($ingredient) {
            return $ingredient->quantity * $ingredient->unit_cost;
        });
        
        // Group by status
        $stockStatus = [
            'In Stock' => $ingredients->where('status', 'In Stock')->count(),
            'Low Stock' => $lowStockItems,
            'Out of Stock' => $outOfStockItems
        ];
        
        return view('reports.inventory', compact(
            'ingredients',
            'totalItems',
            'lowStockItems', 
            'outOfStockItems',
            'totalValue',
            'stockStatus'
        ));
    }

    /**
     * Display daily reports
     */
    public function daily(Request $request)
    {
        $user = Auth::user();
        $branchId = $user->branch_id;
        
        $selectedDate = $request->get('date', Carbon::today());
        $date = Carbon::parse($selectedDate);
        
        // Daily sales summary
        $dailySales = Sale::where('branch_id', $branchId)
            ->whereDate('created_at', $date)
            ->with(['user', 'salesItems.product'])
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
        
        return view('reports.daily', compact(
            'dailySales',
            'totalRevenue',
            'totalTransactions',
            'averageTransaction',
            'paymentMethods',
            'hourlySales',
            'selectedDate'
        ));
    }

    /**
     * Display monthly reports
     */
    public function monthly(Request $request)
    {
        $user = Auth::user();
        $branchId = $user->branch_id;
        
        $selectedMonth = $request->get('month', Carbon::now()->month);
        $selectedYear = $request->get('year', Carbon::now()->year);
        
        // Monthly sales data
        $startOfMonth = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->startOfMonth();
        $endOfMonth = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->endOfMonth();
        
        $monthlySales = Sale::where('branch_id', $branchId)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->with(['salesItems.product'])
            ->get();
        
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
        $productPerformance = SalesItem::whereHas('sale', function($query) use ($branchId, $startOfMonth, $endOfMonth) {
                $query->where('branch_id', $branchId)
                      ->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
            })
            ->select('product_id', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(subtotal) as revenue'))
            ->with('product')
            ->groupBy('product_id')
            ->orderBy('revenue', 'desc')
            ->get();
        
        return view('reports.monthly', compact(
            'totalRevenue',
            'totalTransactions',
            'averageTransaction',
            'dailyBreakdown',
            'productPerformance',
            'selectedMonth',
            'selectedYear'
        ));
    }
}
