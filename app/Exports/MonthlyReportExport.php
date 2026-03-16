<?php

namespace App\Exports;

use App\Models\Sale;
use App\Models\SalesItem;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MonthlyReportExport implements WithMultipleSheets
{
    protected $month;
    protected $year;
    protected $branchId;
    protected $isAll;

    public function __construct($month, $year, $branchId = null)
    {
        $this->month = $month;
        $this->year = $year;
        $this->branchId = $branchId;
        $this->isAll = $branchId === 'all' || $branchId === null;
    }

    public function sheets(): array
    {
        return [
            new MonthlySummarySheet($this->month, $this->year, $this->branchId, $this->isAll),
            new MonthlyTopProductsSheet($this->month, $this->year, $this->branchId, $this->isAll),
            new MonthlyDailyBreakdownSheet($this->month, $this->year, $this->branchId, $this->isAll),
        ];
    }
}

class MonthlySummarySheet implements FromCollection, WithTitle, ShouldAutoSize, WithStyles
{
    protected $month;
    protected $year;
    protected $branchId;
    protected $isAll;

    public function __construct($month, $year, $branchId, $isAll)
    {
        $this->month = $month;
        $this->year = $year;
        $this->branchId = $branchId;
        $this->isAll = $isAll;
    }

    public function collection()
    {
        $startOfMonth = Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth();
        $endOfMonth = Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth();

        $query = Sale::whereBetween('created_at', [$startOfMonth, $endOfMonth]);

        if (!$this->isAll) {
            $query->where('branch_id', $this->branchId);
        }

        $sales = $query->get();

        $totalRevenue = $sales->sum('total_amount');
        $totalTransactions = $sales->count();
        $avgTransaction = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;

        $branchName = $this->isAll ? 'All Branches' : (Branch::find($this->branchId)->name ?? 'Unknown');
        $monthName = Carbon::createFromDate($this->year, $this->month, 1)->format('F Y');

        return collect([
            ['MONTHLY REPORT SUMMARY', ''],
            ['', ''],
            ['Branch', $branchName],
            ['Month', $monthName],
            ['Generated', now()->format('M d, Y h:i A')],
            ['', ''],
            ['METRIC', 'VALUE'],
            ['Total Revenue', '₱' . number_format($totalRevenue, 2)],
            ['Total Transactions', number_format($totalTransactions)],
            ['Average Transaction', '₱' . number_format($avgTransaction, 2)],
        ]);
    }

    public function title(): string
    {
        return 'Summary';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            7 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}

class MonthlyTopProductsSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $month;
    protected $year;
    protected $branchId;
    protected $isAll;

    public function __construct($month, $year, $branchId, $isAll)
    {
        $this->month = $month;
        $this->year = $year;
        $this->branchId = $branchId;
        $this->isAll = $isAll;
    }

    public function collection()
    {
        $startOfMonth = Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth();
        $endOfMonth = Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth();

        $query = SalesItem::whereHas('sale', function ($q) use ($startOfMonth, $endOfMonth) {
            $q->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
            if (!$this->isAll) {
                $q->where('branch_id', $this->branchId);
            }
        })
        ->select('product_id', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(total_price) as revenue'))
        ->with('product.category')
        ->groupBy('product_id')
        ->orderBy('total_sold', 'desc')
        ->limit(10)
        ->get();

        $rank = 1;
        return $query->map(function ($item) use (&$rank) {
            return [
                'rank' => $rank++,
                'product' => $item->product->name ?? 'N/A',
                'category' => $item->product->category->name ?? 'N/A',
                'quantity_sold' => number_format($item->total_sold),
                'revenue' => '₱' . number_format($item->revenue, 2),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Rank',
            'Product',
            'Category',
            'Quantity Sold',
            'Revenue',
        ];
    }

    public function title(): string
    {
        return 'Top 10 Products';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}

class MonthlyDailyBreakdownSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $month;
    protected $year;
    protected $branchId;
    protected $isAll;

    public function __construct($month, $year, $branchId, $isAll)
    {
        $this->month = $month;
        $this->year = $year;
        $this->branchId = $branchId;
        $this->isAll = $isAll;
    }

    public function collection()
    {
        $startOfMonth = Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth();
        $endOfMonth = Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth();

        $query = Sale::whereBetween('created_at', [$startOfMonth, $endOfMonth]);

        if (!$this->isAll) {
            $query->where('branch_id', $this->branchId);
        }

        $sales = $query->get();

        $dailyData = collect();
        $currentDate = $startOfMonth->copy();

        while ($currentDate <= $endOfMonth) {
            $daySales = $sales->filter(function ($sale) use ($currentDate) {
                return $sale->created_at->toDateString() == $currentDate->toDateString();
            });

            $dailyData->push([
                'date' => $currentDate->format('Y-m-d'),
                'day' => $currentDate->format('l'),
                'transactions' => number_format($daySales->count()),
                'revenue' => '₱' . number_format($daySales->sum('total_amount'), 2),
            ]);

            $currentDate->addDay();
        }

        return $dailyData;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Day',
            'Transactions',
            'Revenue',
        ];
    }

    public function title(): string
    {
        $monthName = Carbon::createFromDate($this->year, $this->month, 1)->format('F Y');
        return 'Daily Breakdown (' . $monthName . ')';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
