<?php

namespace App\Exports;

use App\Models\Sale;
use App\Models\Branch;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DailyReportExport implements WithMultipleSheets
{
    protected $date;
    protected $branchId;
    protected $isAll;

    public function __construct($date, $branchId = null)
    {
        $this->date = Carbon::parse($date);
        $this->branchId = $branchId;
        $this->isAll = $branchId === 'all' || $branchId === null;
    }

    public function sheets(): array
    {
        return [
            new DailySummarySheet($this->date, $this->branchId, $this->isAll),
            new DailyPaymentMethodsSheet($this->date, $this->branchId, $this->isAll),
            new DailyAllSalesSheet($this->date, $this->branchId, $this->isAll),
        ];
    }
}

class DailySummarySheet implements FromCollection, WithTitle, ShouldAutoSize, WithStyles
{
    protected $date;
    protected $branchId;
    protected $isAll;

    public function __construct($date, $branchId, $isAll)
    {
        $this->date = $date;
        $this->branchId = $branchId;
        $this->isAll = $isAll;
    }

    public function collection()
    {
        $query = Sale::whereDate('created_at', $this->date);

        if (!$this->isAll) {
            $query->where('branch_id', $this->branchId);
        }

        $sales = $query->get();

        $totalRevenue = $sales->sum('total_amount');
        $totalTransactions = $sales->count();
        $avgTransaction = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;

        $branchName = $this->isAll ? 'All Branches' : (Branch::find($this->branchId)->name ?? 'Unknown');

        return collect([
            ['DAILY REPORT SUMMARY', ''],
            ['', ''],
            ['Branch', $branchName],
            ['Date', $this->date->format('F d, Y')],
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

class DailyPaymentMethodsSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $date;
    protected $branchId;
    protected $isAll;

    public function __construct($date, $branchId, $isAll)
    {
        $this->date = $date;
        $this->branchId = $branchId;
        $this->isAll = $isAll;
    }

    public function collection()
    {
        $query = Sale::whereDate('created_at', $this->date);

        if (!$this->isAll) {
            $query->where('branch_id', $this->branchId);
        }

        $sales = $query->get();

        $paymentMethods = $sales->groupBy('payment_method')
            ->map(function ($group, $method) {
                return [
                    'method' => ucfirst($method ?? 'cash'),
                    'count' => number_format($group->count()),
                    'total' => '₱' . number_format($group->sum('total_amount'), 2),
                ];
            })->values();

        return $paymentMethods;
    }

    public function headings(): array
    {
        return [
            'Payment Method',
            'Transactions',
            'Total Amount',
        ];
    }

    public function title(): string
    {
        return 'Payment Methods';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}

class DailyAllSalesSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    protected $date;
    protected $branchId;
    protected $isAll;

    public function __construct($date, $branchId, $isAll)
    {
        $this->date = $date;
        $this->branchId = $branchId;
        $this->isAll = $isAll;
    }

    public function collection()
    {
        $query = Sale::with(['user', 'branch', 'salesItems.product'])
            ->whereDate('created_at', $this->date)
            ->orderBy('created_at', 'desc');

        if (!$this->isAll) {
            $query->where('branch_id', $this->branchId);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Time',
            'Transaction ID',
            'Branch',
            'Cashier',
            'Items',
            'Payment Method',
            'Total (₱)',
        ];
    }

    public function map($sale): array
    {
        $items = $sale->salesItems->map(function ($item) {
            return $item->product->name . ' x' . $item->quantity;
        })->implode(', ');

        return [
            $sale->created_at->format('H:i:s'),
            $sale->id,
            $sale->branch->name ?? 'N/A',
            $sale->user->name ?? 'N/A',
            $items,
            ucfirst($sale->payment_method ?? 'cash'),
            number_format($sale->total_amount, 2),
        ];
    }

    public function title(): string
    {
        return 'All Sales (' . $this->date->format('M d, Y') . ')';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
