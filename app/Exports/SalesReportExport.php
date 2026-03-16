<?php

namespace App\Exports;

use App\Models\Sale;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportExport implements WithMultipleSheets
{
    protected $startDate;
    protected $endDate;
    protected $branchId;
    protected $isAll;

    public function __construct($startDate, $endDate, $branchId = null)
    {
        $this->startDate = Carbon::parse($startDate)->startOfDay();
        $this->endDate = Carbon::parse($endDate)->endOfDay();
        $this->branchId = $branchId;
        $this->isAll = $branchId === 'all' || $branchId === null;
    }

    public function sheets(): array
    {
        return [
            new SalesSummarySheet($this->startDate, $this->endDate, $this->branchId, $this->isAll),
            new SalesByStaffSheet($this->startDate, $this->endDate, $this->branchId, $this->isAll),
            new AllSalesSheet($this->startDate, $this->endDate, $this->branchId, $this->isAll),
        ];
    }
}

class SalesSummarySheet implements FromCollection, WithTitle, ShouldAutoSize, WithStyles
{
    protected $startDate;
    protected $endDate;
    protected $branchId;
    protected $isAll;

    public function __construct($startDate, $endDate, $branchId, $isAll)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->branchId = $branchId;
        $this->isAll = $isAll;
    }

    public function collection()
    {
        $query = Sale::whereBetween('created_at', [$this->startDate, $this->endDate]);

        if (!$this->isAll) {
            $query->where('branch_id', $this->branchId);
        }

        $sales = $query->get();

        $totalRevenue = $sales->sum('total_amount');
        $totalTransactions = $sales->count();
        $avgTransaction = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;

        $branchName = $this->isAll ? 'All Branches' : (Branch::find($this->branchId)->name ?? 'Unknown');

        return collect([
            ['SALES REPORT SUMMARY', ''],
            ['', ''],
            ['Branch', $branchName],
            ['Period', $this->startDate->format('M d, Y') . ' - ' . $this->endDate->format('M d, Y')],
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

class SalesByStaffSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $startDate;
    protected $endDate;
    protected $branchId;
    protected $isAll;

    public function __construct($startDate, $endDate, $branchId, $isAll)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->branchId = $branchId;
        $this->isAll = $isAll;
    }

    public function collection()
    {
        $query = Sale::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->with('user');

        if (!$this->isAll) {
            $query->where('branch_id', $this->branchId);
        }

        $salesByUser = $query
            ->select('user_id', DB::raw('COUNT(*) as transaction_count'), DB::raw('SUM(total_amount) as total_sales'))
            ->groupBy('user_id')
            ->orderBy('total_sales', 'desc')
            ->get();

        return $salesByUser->map(function ($item) {
            $user = \App\Models\User::find($item->user_id);
            return [
                'staff' => $user->name ?? 'Unknown',
                'transactions' => number_format($item->transaction_count),
                'total_sales' => '₱' . number_format($item->total_sales, 2),
                'average' => '₱' . number_format($item->transaction_count > 0 ? $item->total_sales / $item->transaction_count : 0, 2),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Staff Name',
            'Transactions',
            'Total Sales',
            'Average per Transaction',
        ];
    }

    public function title(): string
    {
        return 'Sales by Staff';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}

class AllSalesSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    protected $startDate;
    protected $endDate;
    protected $branchId;
    protected $isAll;

    public function __construct($startDate, $endDate, $branchId, $isAll)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->branchId = $branchId;
        $this->isAll = $isAll;
    }

    public function collection()
    {
        $query = Sale::with(['user', 'branch', 'salesItems.product'])
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->orderBy('created_at', 'desc');

        if (!$this->isAll) {
            $query->where('branch_id', $this->branchId);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Transaction ID',
            'Date & Time',
            'Branch',
            'Cashier',
            'Items',
            'Payment Method',
            'Subtotal (₱)',
            'Discount (₱)',
            'Total (₱)',
        ];
    }

    public function map($sale): array
    {
        $items = $sale->salesItems->map(function ($item) {
            return $item->product->name . ' x' . $item->quantity;
        })->implode(', ');

        return [
            $sale->id,
            $sale->created_at->format('Y-m-d H:i:s'),
            $sale->branch->name ?? 'N/A',
            $sale->user->name ?? 'N/A',
            $items,
            ucfirst($sale->payment_method ?? 'cash'),
            number_format($sale->subtotal ?? $sale->total_amount, 2),
            number_format($sale->discount ?? 0, 2),
            number_format($sale->total_amount, 2),
        ];
    }

    public function title(): string
    {
        return 'All Sales';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
