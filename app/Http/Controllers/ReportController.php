<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\SalesItem;
use App\Models\Ingredient;
use App\Models\IngredientInventory;
use App\Models\InventoryImportHistory;
use App\Models\Branch;
use App\Models\BranchSession;
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
use App\Exports\InventoryImportTemplateExport;

class ReportController extends Controller
{
    private const INVENTORY_IMPORT_SESSION_KEY = 'inventory_import_preview';

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

    private function getInventoryBranchFilter(Request $request): array
    {
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
        $importPreview = session(self::INVENTORY_IMPORT_SESSION_KEY);
        $normalizeStatus = function (string $status): array {
            return match ($status) {
                'Out of Stock' => ['No Stock', 0],
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
            $rawStatus = $inventory ? $ingredient->getStatusForBranch($branchId) : 'Out of Stock';
            [$ingredient->branch_status, $ingredient->status_priority] = $normalizeStatus($rawStatus);
            $ingredient->branch_min_stock = $inventory ? $inventory->min_stock_level : 0;
            $ingredient->branch_last_updated = $inventory?->updated_at;
        });

        $ingredients = $ingredients
            ->sortBy(function ($ingredient) {
                return sprintf('%02d-%s', $ingredient->status_priority ?? 9, strtolower($ingredient->name));
            })
            ->values();
        
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
            'stockStatus',
            'importPreview'
        ), $branchFilter));
    }

    public function downloadInventoryImportTemplate()
    {
        if (!class_exists(\ZipArchive::class)) {
            return response()->streamDownload(function () {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Item Name', 'Qty', 'Unit', 'Branch']);
                fputcsv($handle, ['Milk', 20, 'L', '']);
                fputcsv($handle, ['Sugar', 10, 'KG', '']);
                fputcsv($handle, ['C2', 50, 'PCS', '']);
                fclose($handle);
            }, 'inventory_import_template.csv', [
                'Content-Type' => 'text/csv',
            ]);
        }

        return Excel::download(
            new InventoryImportTemplateExport(),
            'inventory_import_template.xlsx'
        );
    }

    public function previewInventoryImport(Request $request)
    {
        $request->validate([
            'inventory_file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $previousPreview = session(self::INVENTORY_IMPORT_SESSION_KEY);
        if ($previousPreview && !empty($previousPreview['stored_path'])) {
            Storage::delete($previousPreview['stored_path']);
        }

        $uploadedFile = $request->file('inventory_file');
        $storedPath = $uploadedFile->store('inventory-imports');
        $originalName = $uploadedFile->getClientOriginalName();

        try {
            $rows = $this->readInventoryImportRows($uploadedFile);
        } catch (\Throwable $exception) {
            Storage::delete($storedPath);

            return back()->with('error', $exception->getMessage());
        }

        $headings = array_map(fn ($heading) => $this->normalizeHeading($heading), array_shift($rows) ?? []);
        $selectedBranchId = $request->input('branch_id');

        $previewRows = [];
        $seenRows = [];
        $errorCount = 0;

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $data = $this->combineImportRow($headings, $row);

            if ($this->isEmptyImportRow($data)) {
                continue;
            }

            $validatedRow = $this->validateInventoryImportRow($data, $seenRows, $rowNumber, $selectedBranchId);
            if (!$validatedRow['valid']) {
                $errorCount++;
            }

            $previewRows[] = $validatedRow;
        }

        if (empty($previewRows)) {
            Storage::delete($storedPath);

            return back()->with('error', 'The uploaded file has no inventory rows to import.');
        }

        $preview = [
            'rows' => $previewRows,
            'error_count' => $errorCount,
            'valid_count' => count($previewRows) - $errorCount,
            'file_name' => $originalName,
            'stored_path' => $storedPath,
            'created_at' => now()->toDateTimeString(),
        ];

        session([self::INVENTORY_IMPORT_SESSION_KEY => $preview]);

        return redirect()
            ->route('reports.inventory', $request->only('branch_id'));
    }

    public function confirmInventoryImport(Request $request)
    {
        $preview = session(self::INVENTORY_IMPORT_SESSION_KEY);

        if (!$preview) {
            return back()->with('error', 'Please upload and preview an inventory file first.');
        }

        if (($preview['error_count'] ?? 0) > 0) {
            return back()->with('error', 'Please fix the import errors before confirming.');
        }

        $admin = Auth::user();
        $importedAt = now();
        $updatedCount = 0;

        DB::transaction(function () use ($preview, $admin, $importedAt, &$updatedCount) {
            foreach ($preview['rows'] as $row) {
                $inventory = IngredientInventory::firstOrCreate(
                    [
                        'ingredient_id' => $row['ingredient_id'],
                        'branch_id' => $row['branch_id'],
                    ],
                    [
                        'quantity' => 0,
                        'min_stock_level' => 0,
                    ]
                );

                $previousQty = (float) $inventory->quantity;
                $addedQty = (float) $row['qty'];
                $finalQty = $previousQty + $addedQty;

                $inventory->quantity = $finalQty;
                $inventory->save();

                InventoryImportHistory::create([
                    'imported_at' => $importedAt,
                    'admin_id' => $admin->id,
                    'ingredient_id' => $row['ingredient_id'],
                    'branch_id' => $row['branch_id'],
                    'supplier' => $row['supplier'],
                    'imported_file' => $preview['file_name'],
                    'previous_qty' => $previousQty,
                    'added_qty' => $addedQty,
                    'final_qty' => $finalQty,
                ]);

                $updatedCount++;
            }
        });

        session()->forget(self::INVENTORY_IMPORT_SESSION_KEY);

        return redirect()
            ->route('reports.inventory', $request->only('branch_id'))
            ->with('success', "Inventory import confirmed. {$updatedCount} item(s) updated and recorded in stock history.");
    }

    public function cancelInventoryImport(Request $request)
    {
        $preview = session(self::INVENTORY_IMPORT_SESSION_KEY);

        if ($preview && !empty($preview['stored_path'])) {
            Storage::delete($preview['stored_path']);
        }

        session()->forget(self::INVENTORY_IMPORT_SESSION_KEY);

        return redirect()
            ->route('reports.inventory', $request->only('branch_id'));
    }

    private function validateInventoryImportRow(array $data, array &$seenRows, int $rowNumber, $selectedBranchId = null): array
    {
        $itemName = trim((string) ($data['item_name'] ?? ''));
        $qty = $data['qty'] ?? null;
        $unit = trim((string) ($data['unit'] ?? ''));
        $branchName = trim((string) ($data['branch'] ?? ''));
        $supplier = trim((string) ($data['supplier'] ?? 'N/A')) ?: 'N/A';
        $errors = [];

        $ingredient = $itemName !== ''
            ? Ingredient::whereRaw('LOWER(name) = ?', [strtolower($itemName)])->where('is_active', true)->first()
            : null;

        $branch = $branchName !== ''
            ? Branch::whereRaw('LOWER(name) = ?', [strtolower($branchName)])->where('is_active', true)->first()
            : null;

        if (!$ingredient) {
            $errors[] = 'Item does not exist or is inactive.';
        }

        if (!$branch) {
            $errors[] = 'Branch is invalid or inactive.';
        } elseif ($selectedBranchId && $selectedBranchId !== 'all' && (int) $selectedBranchId !== (int) $branch->id) {
            $errors[] = 'Branch must match the selected report branch.';
        }

        if (!is_numeric($qty) || (float) $qty <= 0) {
            $errors[] = 'Quantity must be greater than zero.';
        }

        if ($ingredient) {
            $uploadedUnit = Ingredient::normalizeUnit($unit);
            $ingredientUnit = Ingredient::normalizeUnit($ingredient->unit);

            if (!$uploadedUnit || $uploadedUnit !== $ingredientUnit) {
                $errors[] = "Unit must match {$ingredient->unit}.";
            }
        } elseif ($unit === '') {
            $errors[] = 'Unit is required.';
        }

        $duplicateKey = strtolower($itemName . '|' . $branchName);
        if (isset($seenRows[$duplicateKey])) {
            $errors[] = "Duplicate row for the same item and branch. First seen on row {$seenRows[$duplicateKey]}.";
        } else {
            $seenRows[$duplicateKey] = $rowNumber;
        }

        return [
            'row_number' => $rowNumber,
            'item_name' => $itemName,
            'qty' => is_numeric($qty) ? (float) $qty : $qty,
            'unit' => $unit,
            'branch' => $branchName,
            'supplier' => $supplier,
            'ingredient_id' => $ingredient?->id,
            'branch_id' => $branch?->id,
            'valid' => empty($errors),
            'status' => empty($errors) ? 'Valid' : 'Error',
            'errors' => $errors,
        ];
    }

    private function readInventoryImportRows($uploadedFile): array
    {
        $extension = strtolower($uploadedFile->getClientOriginalExtension());

        if (in_array($extension, ['xlsx', 'xls'], true) && !class_exists(\ZipArchive::class)) {
            throw new \RuntimeException('Excel .xlsx/.xls import requires the PHP zip extension. Please upload a CSV file or enable zip in PHP.');
        }

        if ($extension === 'csv') {
            $rows = [];
            $handle = fopen($uploadedFile->getRealPath(), 'r');

            if ($handle === false) {
                throw new \RuntimeException('Unable to read the uploaded CSV file.');
            }

            while (($row = fgetcsv($handle)) !== false) {
                $rows[] = $row;
            }

            fclose($handle);

            return $rows;
        }

        return Excel::toArray([], $uploadedFile)[0] ?? [];
    }

    private function combineImportRow(array $headings, array $row): array
    {
        $combined = [];

        foreach ($headings as $index => $heading) {
            if ($heading === '') {
                continue;
            }

            $combined[$heading] = $row[$index] ?? null;
        }

        return $combined;
    }

    private function isEmptyImportRow(array $data): bool
    {
        return collect($data)->filter(fn ($value) => trim((string) $value) !== '')->isEmpty();
    }

    private function normalizeHeading($heading): string
    {
        $normalized = strtolower(trim((string) $heading));
        $normalized = preg_replace('/[^a-z0-9]+/', '_', $normalized);
        $normalized = trim($normalized, '_');

        return match ($normalized) {
            'item', 'item_name', 'ingredient', 'ingredient_name', 'name' => 'item_name',
            'quantity', 'qty', 'added_qty', 'add_qty' => 'qty',
            'uom', 'unit' => 'unit',
            'branch', 'branch_name' => 'branch',
            'supplier', 'supplier_name' => 'supplier',
            default => $normalized,
        };
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
