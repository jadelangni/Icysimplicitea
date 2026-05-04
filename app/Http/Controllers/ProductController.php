<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Ingredient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Services\CatalogInventorySyncService;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Show product management page
        $user = Auth::user();
        $branchId = $user->branch_id;
        $search = trim((string) $request->input('search', ''));

        $categories = Category::where('is_active', true)->get();

        $productsQuery = Product::with([
            'category',
            'inventory' => function ($query) use ($user, $branchId) {
                if (!$user->isAdmin()) {
                    $query->where('branch_id', $branchId);
                }
            },
        ]);

        if ($search !== '') {
            $productsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhereHas('category', function ($categoryQuery) use ($search) {
                        $categoryQuery->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        $products = $productsQuery->orderBy('name')->get();

        return view('products.index', compact('products', 'categories', 'search'));
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
            'price' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'sometimes|boolean',
            'options' => 'nullable|json',
            'product_type' => 'nullable|in:direct,composite',
        ]);

        $parsedOptions = $this->parseOptions($request->input('options'));
        $requiresBasePrice = $this->optionsRequireBasePrice($parsedOptions);

        if (($data['price'] === null || $data['price'] === '') && $requiresBasePrice) {
            return back()->withErrors(['price' => 'Base price is required for products without fixed variant prices.'])->withInput();
        }

        if (($data['price'] === null || $data['price'] === '') && !$requiresBasePrice) {
            $derivedPrice = $this->getMinimumVariantPrice($parsedOptions);
            if ($derivedPrice !== null) {
                $data['price'] = $derivedPrice;
            }
        }

        if ($data['price'] === null || $data['price'] === '') {
            $data['price'] = 0;
        }

        $data['options'] = $parsedOptions;

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
        $data['product_type'] = $data['product_type'] ?? Product::TYPE_DIRECT;

        $result = DB::transaction(function () use ($data) {
            $product = Product::create($data);
            $initializedBranchCount = app(CatalogInventorySyncService::class)
                ->ensureDirectProductInventory($product);

            return [
                'product' => $product,
                'initialized_branch_count' => $initializedBranchCount,
            ];
        });

        $successMessage = 'Product created successfully.';
        if ($result['product']->isDirectProduct()) {
            $successMessage .= ' Inventory initialized in ' . $result['initialized_branch_count'] . ' branch(es).';
        }

        return redirect()->route('products.index')->with('success', $successMessage);
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

        return view('products.edit', compact('product', 'categories'));
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
            'price' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'sometimes|boolean',
            'options' => 'nullable|json',
            'product_type' => 'nullable|in:direct,composite',
        ]);

        $parsedOptions = $this->parseOptions($request->input('options'));
        $requiresBasePrice = $this->optionsRequireBasePrice($parsedOptions);

        if (($data['price'] === null || $data['price'] === '') && $requiresBasePrice) {
            return back()->withErrors(['price' => 'Base price is required for products without fixed variant prices.'])->withInput();
        }

        if (($data['price'] === null || $data['price'] === '') && !$requiresBasePrice) {
            $derivedPrice = $this->getMinimumVariantPrice($parsedOptions);
            if ($derivedPrice !== null) {
                $data['price'] = $derivedPrice;
            }
        }

        if ($data['price'] === null || $data['price'] === '') {
            $data['price'] = 0;
        }

        $data['options'] = $parsedOptions;

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

        DB::transaction(function () use ($product, $data, $request) {
            $product->update($data);

            // Handle recipe/ingredients only when the product form explicitly sends recipe data.
            if ($request->filled('recipe')) {
                $recipeData = json_decode($request->input('recipe'), true);
                $syncData = [];

                if (is_array($recipeData)) {
                    foreach ($recipeData as $item) {
                        if (!empty($item['ingredient_id']) && !empty($item['quantity_required'])) {
                            $ingredient = Ingredient::find($item['ingredient_id']);
                            $syncData[$item['ingredient_id']] = [
                                'quantity_required' => $item['quantity_required'],
                                'unit' => $item['unit'] ?? $ingredient?->getRecipeUnit(),
                            ];
                        }
                    }
                }

                $product->ingredients()->sync($syncData);
            }

            app(CatalogInventorySyncService::class)->ensureDirectProductInventory($product);
        });

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

    private function parseOptions(?string $rawOptions): ?array
    {
        if (!$rawOptions) {
            return null;
        }

        $decoded = json_decode($rawOptions, true);

        return is_array($decoded) ? $decoded : null;
    }

    private function optionsRequireBasePrice(?array $options): bool
    {
        if (empty($options)) {
            return true;
        }

        $hasAnyValues = false;

        foreach ($options as $option) {
            $values = $option['values'] ?? [];
            if (!is_array($values) || empty($values)) {
                continue;
            }

            foreach ($values as $value) {
                $hasAnyValues = true;

                if (!is_array($value)) {
                    return true;
                }

                if (!array_key_exists('price', $value) || $value['price'] === null || $value['price'] === '' || (float) $value['price'] == 0.0) {
                    return true;
                }
            }
        }

        return !$hasAnyValues;
    }

    private function getMinimumVariantPrice(?array $options): ?float
    {
        if (empty($options)) {
            return null;
        }

        $minPrice = null;

        foreach ($options as $option) {
            $values = $option['values'] ?? [];
            if (!is_array($values)) {
                continue;
            }

            foreach ($values as $value) {
                if (!is_array($value) || !array_key_exists('price', $value)) {
                    continue;
                }

                if ($value['price'] === null || $value['price'] === '') {
                    continue;
                }

                $numericPrice = (float) $value['price'];
                if ($minPrice === null || $numericPrice < $minPrice) {
                    $minPrice = $numericPrice;
                }
            }
        }

        return $minPrice;
    }
}
