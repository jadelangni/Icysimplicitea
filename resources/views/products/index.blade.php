<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Products') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl transition-colors duration-200">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Product List</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage your products and their sizes</p>
                        </div>
                        <a href="{{ route('products.create') }}" class="inline-flex items-center px-5 py-2.5 bg-simplicitea-600 text-white rounded-xl hover:bg-simplicitea-700 font-medium transition-colors shadow-sm">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            New Product
                        </a>
                    </div>

                    @if($products->isEmpty())
                        <div class="text-center py-16">
                            <div class="text-6xl mb-4">🧋</div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No products yet</h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-4">Get started by adding your first product</p>
                            <a href="{{ route('products.create') }}" class="inline-flex items-center px-4 py-2 bg-simplicitea-600 text-white rounded-lg hover:bg-simplicitea-700">
                                Add Product
                            </a>
                        </div>
                    @else
                        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr class="text-gray-600 dark:text-gray-300">
                                        <th class="py-4 px-4 font-semibold">Product</th>
                                        <th class="py-4 px-4 font-semibold">Category</th>
                                        <th class="py-4 px-4 font-semibold">Sizes / Price</th>
                                        <th class="py-4 px-4 font-semibold">Status</th>
                                        <th class="py-4 px-4 font-semibold text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @foreach($products as $product)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                        <td class="py-4 px-4">
                                            <div class="flex items-center gap-3">
                                                @if($product->image)
                                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-10 h-10 rounded-lg object-cover flex-shrink-0">
                                                @else
                                                    <div class="w-10 h-10 bg-gradient-to-br from-simplicitea-100 to-simplicitea-200 dark:from-gray-600 dark:to-gray-700 rounded-lg flex items-center justify-center flex-shrink-0">
                                                        <span class="text-lg">🧋</span>
                                                    </div>
                                                @endif
                                                <span class="font-medium text-gray-900 dark:text-white">{{ $product->name }}</span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                                {{ $product->category->name ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-4">
                                            @if($product->options && is_array($product->options) && !empty($product->options))
                                                @foreach($product->options as $option)
                                                    @if(isset($option['name']) && $option['name'] === 'Size' && isset($option['values']) && is_array($option['values']))
                                                        <div class="flex flex-wrap gap-1">
                                                            @foreach($option['values'] as $value)
                                                                @if(isset($value['label']))
                                                                    @php
                                                                        $priceVal = isset($value['price']) ? floatval($value['price']) : 0;
                                                                    @endphp
                                                                    <span class="inline-flex items-center bg-simplicitea-50 dark:bg-simplicitea-900/30 text-simplicitea-700 dark:text-simplicitea-300 px-2 py-1 rounded-lg text-xs font-medium">
                                                                        {{ $value['label'] }} • ₱{{ number_format($priceVal, 0) }}
                                                                    </span>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @else
                                                <span class="text-lg font-bold text-gray-900 dark:text-white">₱{{ number_format($product->price, 0) }}</span>
                                            @endif
                                        </td>
                                        @php $qty = $product->inventory->first()->quantity ?? 0; @endphp
                                        <td class="py-4 px-4">
                                            @if($product->is_active && $qty > 0)
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300">
                                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                                    In Stock
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300">
                                                    <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                                                    Out of Stock
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-4 px-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('products.edit', $product->id) }}" class="inline-flex items-center px-3 py-1.5 bg-simplicitea-50 dark:bg-simplicitea-900/30 text-simplicitea-700 dark:text-simplicitea-300 rounded-lg text-xs font-medium hover:bg-simplicitea-100 dark:hover:bg-simplicitea-900/50 transition-colors">
                                                    Edit
                                                </a>
                                                <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this product?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-lg text-xs font-medium hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
