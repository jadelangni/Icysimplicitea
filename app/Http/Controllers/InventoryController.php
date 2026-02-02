<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\SalesItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $branchId = Auth::user()->branch_id;

        $inventories = Inventory::with('product')
            ->where('branch_id', $branchId)
            ->get();

        // Sales report for last 30 days: total quantity sold per product for this branch
        $start = now()->subDays(30)->startOfDay();
        $end = now()->endOfDay();

        $salesReport = SalesItem::select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->join('sales', 'sales.id', '=', 'sales_items.sale_id')
            ->where('sales.branch_id', $branchId)
            ->whereBetween('sales.created_at', [$start, $end])
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->get()
            ->map(function($row) {
                $product = \App\Models\Product::find($row->product_id);
                return [
                    'product' => $product ? $product->name : 'Unknown',
                    'quantity' => (int)$row->total_quantity
                ];
            });

        $chartLabels = $salesReport->pluck('product');
        $chartData = $salesReport->pluck('quantity');

        return view('inventory.index', compact('inventories', 'chartLabels', 'chartData'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
