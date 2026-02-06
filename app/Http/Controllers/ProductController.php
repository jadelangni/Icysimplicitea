<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Ingredient;
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
            'category_id' => 'nullable|exists:categories,id',
            'custom_category' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'sometimes|boolean',
            'options' => 'nullable|json'
        ]);

        // Handle custom category creation
        if (!empty($data['custom_category'])) {
            $category = Category::create([
                'name' => $data['custom_category'],
                'is_active' => true
            ]);
            $data['category_id'] = $category->id;
        }
        
        // Validate that we have a category
        if (empty($data['category_id'])) {
            return back()->withErrors(['category_id' => 'Please select a category or create a new one.'])->withInput();
        }
        
        unset($data['custom_category']);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $data['image'] = $path;
        }

        $data['is_active'] = isset($data['is_active']) ? (bool)$data['is_active'] : true;

        $product = Product::create($data);

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
        $product = Product::with('ingredients')->findOrFail($id);
        $categories = Category::where('is_active', true)->get();
        $ingredients = Ingredient::where('is_active', true)->orderBy('name')->get();
        
        // Format existing recipe for the view
        $recipe = $product->ingredients->map(function($ingredient) {
            return [
                'ingredient_id' => $ingredient->id,
                'name' => $ingredient->name,
                'quantity_required' => $ingredient->pivot->quantity_required,
                'unit' => $ingredient->pivot->unit ?? $ingredient->unit,
            ];
        });

        return view('products.edit', compact('product', 'categories', 'ingredients', 'recipe'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $data = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'custom_category' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'sometimes|boolean'
        ]);

        // Handle custom category creation
        if (!empty($data['custom_category'])) {
            $category = Category::create([
                'name' => $data['custom_category'],
                'is_active' => true
            ]);
            $data['category_id'] = $category->id;
        }
        
        // Validate that we have a category
        if (empty($data['category_id'])) {
            return back()->withErrors(['category_id' => 'Please select a category or create a new one.'])->withInput();
        }
        
        unset($data['custom_category']);

        if ($request->hasFile('image')) {
            // delete old image if exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $path = $request->file('image')->store('products', 'public');
            $data['image'] = $path;
        }

        $data['is_active'] = isset($data['is_active']) ? (bool)$data['is_active'] : false;
        // handle options input (JSON string coming from form)
        if ($request->filled('options')) {
            $opts = json_decode($request->input('options'), true);
            $data['options'] = $opts ?: null;
        }
        $product->update($data);

        // Handle recipe/ingredients
        if ($request->filled('recipe')) {
            $recipeData = json_decode($request->input('recipe'), true);
            $syncData = [];
            
            if (is_array($recipeData)) {
                foreach ($recipeData as $item) {
                    if (!empty($item['ingredient_id']) && !empty($item['quantity_required'])) {
                        $syncData[$item['ingredient_id']] = [
                            'quantity_required' => $item['quantity_required'],
                            'unit' => $item['unit'] ?? null,
                        ];
                    }
                }
            }
            
            $product->ingredients()->sync($syncData);
        } else {
            // Clear recipe if empty
            $product->ingredients()->detach();
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
