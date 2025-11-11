<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Products') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Product List</h3>
                        <a href="{{ route('products.create') }}" class="inline-flex items-center px-4 py-2 bg-simplicitea-600 text-white rounded-lg hover:bg-simplicitea-700">+ New Product</a>
                    </div>

                    @if($products->isEmpty())
                        <div class="text-center py-16 text-gray-500">
                            No products found.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="text-gray-600 border-b">
                                        <th class="py-3 px-2">Name</th>
                                        <th class="py-3 px-2">Category</th>
                                        <th class="py-3 px-2">Price</th>
                                        <th class="py-3 px-2">Stock</th>
                                        <th class="py-3 px-2">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $product)
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="py-3 px-2">{{ $product->name }}</td>
                                        <td class="py-3 px-2">{{ $product->category->name ?? '-' }}</td>
                                        <td class="py-3 px-2">₱{{ number_format($product->price, 2) }}</td>
                                        @php $qty = $product->inventory->first()->quantity ?? 0; @endphp
                                        <td class="py-3 px-2">
                                            @if($product->is_active && $qty > 0)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Available</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Unavailable</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-2">
                                            <a href="{{ route('products.edit', $product->id) }}" class="text-simplicitea-600 hover:underline mr-3">Edit</a>
                                            <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this product?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                            </form>
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
