<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Product') }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-4">
                        @php $qty = isset($branchStock) ? $branchStock : ($product->inventory->first()->quantity ?? 0); @endphp
                        @if($product->is_active && $qty > 0)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">Available</span>
                        @else
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">Unavailable</span>
                        @endif
                    </div>
                    @if($errors->any())
                        <div class="mb-4 text-red-600">
                            <ul class="list-disc pl-5">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('products.update', $product->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 gap-4">
                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Category</span>
                                <select name="category_id" class="mt-1 block w-full rounded-md border-gray-300">
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @if($product->category_id == $category->id) selected @endif>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Name</span>
                                <input type="text" name="name" value="{{ old('name', $product->name) }}" class="mt-1 block w-full rounded-md border-gray-300" required>
                            </label>

                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Description</span>
                                <textarea name="description" class="mt-1 block w-full rounded-md border-gray-300">{{ old('description', $product->description) }}</textarea>
                            </label>

                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Price</span>
                                <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" class="mt-1 block w-full rounded-md border-gray-300" required>
                            </label>

                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Image (optional)</span>
                                <input type="file" name="image" class="mt-1 block w-full">
                                @if($product->image)
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-24 h-24 object-cover rounded">
                                    </div>
                                @endif
                            </label>

                            <!-- send explicit false when unchecked, and 1 when checked -->
                            <input type="hidden" name="is_active" value="0">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="is_active" value="1" class="form-checkbox" @if($product->is_active) checked @endif>
                                <span class="ml-2 text-sm text-gray-700">Active</span>
                            </label>

                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Stock (for your branch)</span>
                                <input type="number" name="stock" value="{{ old('stock', $branchStock ?? ($product->inventory->first()->quantity ?? 0)) }}" class="mt-1 block w-full rounded-md border-gray-300" min="0">
                            </label>

                            <div class="pt-4">
                                <button type="submit" class="px-4 py-2 bg-simplicitea-600 text-white rounded-lg">Update</button>
                                <a href="{{ route('products.index') }}" class="ml-3 text-sm text-gray-600">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
