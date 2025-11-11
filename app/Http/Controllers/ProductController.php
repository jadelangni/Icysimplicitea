<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Inventory;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Show product management page
        $branchId = Auth::user()->branch_id;

        $categories = Category::where('is_active', true)->get();

        $products = Product::with(['category', 'inventory' => function($query) use ($branchId) {
            $query->where('branch_id', $branchId);
        }])->get();

        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        return view('products.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'sometimes|boolean',
            'initial_stock' => 'nullable|integer|min:0'
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $data['image'] = $path;
        }

        $data['is_active'] = isset($data['is_active']) ? (bool)$data['is_active'] : true;

        $product = Product::create($data);

        // create initial inventory record for the current branch
        $branchId = Auth::user()->branch_id;
        $initialStock = isset($data['initial_stock']) ? (int)$data['initial_stock'] : 0;

        Inventory::create([
            'branch_id' => $branchId,
            'product_id' => $product->id,
            'quantity' => $initialStock,
            'min_stock_level' => 0
        ]);

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $product = Product::with('category', 'inventory')->findOrFail($id);
        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::where('is_active', true)->get();
        $branchId = Auth::user()->branch_id;
        $branchInventory = $product->inventory()->where('branch_id', $branchId)->first();
        $branchStock = $branchInventory ? $branchInventory->quantity : 0;

        return view('products.edit', compact('product', 'categories', 'branchStock'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'sometimes|boolean',
            'stock' => 'nullable|integer|min:0'
        ]);

        if ($request->hasFile('image')) {
            // delete old image if exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $path = $request->file('image')->store('products', 'public');
            $data['image'] = $path;
        }

        $data['is_active'] = isset($data['is_active']) ? (bool)$data['is_active'] : false;

        $product->update($data);

        // update or create inventory for current branch
        $branchId = Auth::user()->branch_id;
        if (isset($data['stock'])) {
            $inventory = Inventory::firstOrNew([
                'branch_id' => $branchId,
                'product_id' => $product->id
            ]);
            $inventory->quantity = (int)$data['stock'];
            $inventory->min_stock_level = $inventory->min_stock_level ?? 0;
            $inventory->save();
        }

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted.');
    }

    /**
     * Toggle availability (is_active) for the product.
     */
    public function toggleAvailability($id)
    {
        $product = Product::findOrFail($id);
        $product->is_active = !$product->is_active;
        $product->save();

        return back()->with('success', 'Product availability updated.');
    }
}
