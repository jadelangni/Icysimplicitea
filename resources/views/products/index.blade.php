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
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-black">Product List</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage your products and their sizes</p>
                        </div>
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
                            <div class="relative w-full sm:w-72">
                                <input
                                    id="productSearchInput"
                                    type="text"
                                    value="{{ $search ?? '' }}"
                                    placeholder="Search products..."
                                    class="w-full pl-10 pr-10 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-simplicitea-500 focus:border-simplicitea-500"
                                >
                                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <button id="clearProductSearch" type="button" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-sm">✕</button>
                            </div>

                            <button type="button" id="openCreateProductModal" class="inline-flex items-center justify-center px-5 py-2.5 bg-simplicitea-600 text-black rounded-xl hover:bg-simplicitea-700 font-medium transition-colors shadow-sm whitespace-nowrap">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                New Product
                            </button>
                        </div>
                    </div>

                    @if($products->isEmpty())
                        <div class="text-center py-16">
                            <div class="text-6xl mb-4">🧋</div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-black mb-2">
                                {{ !empty($search) ? 'No matching products' : 'No products yet' }}
                            </h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-4">
                                {{ !empty($search) ? 'Try a different search term.' : 'Get started by adding your first product' }}
                            </p>
                            <button type="button" id="openCreateProductModalEmpty" class="inline-flex items-center px-4 py-2 bg-simplicitea-600 text-black rounded-lg hover:bg-simplicitea-700">
                                Add Product
                            </button>
                        </div>
                    @else
                        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                            <div style="min-width: 940px; width: max-content;">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr class="text-gray-600 dark:text-gray-300">
                                        <th class="py-4 px-4 font-semibold whitespace-nowrap">Product</th>
                                        <th class="py-4 px-4 font-semibold whitespace-nowrap">Category</th>
                                        <th class="py-4 px-4 font-semibold whitespace-nowrap">Sizes / Price</th>
                                        <th class="py-4 px-4 font-semibold whitespace-nowrap">Status</th>
                                        <th class="py-4 px-4 font-semibold text-right whitespace-nowrap">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="productsTableBody" class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @foreach($products as $product)
                                    <tr class="product-row hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                        <td class="py-4 px-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                @if($product->image)
                                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-10 h-10 rounded-lg object-cover flex-shrink-0">
                                                @else
                                                    <div class="w-10 h-10 bg-gradient-to-br from-simplicitea-100 to-simplicitea-200 dark:from-gray-600 dark:to-gray-700 rounded-lg flex items-center justify-center flex-shrink-0">
                                                        <span class="text-lg">🧋</span>
                                                    </div>
                                                @endif
                                                <span class="font-medium text-gray-900 dark:text-black">{{ $product->name }}</span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                                {{ $product->category->name ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 whitespace-nowrap">
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
                                                <span class="text-lg font-bold text-gray-900 dark:text-black">₱{{ number_format($product->price, 0) }}</span>
                                            @endif
                                        </td>
                                        @php $qty = $product->inventory->first()->quantity ?? 0; @endphp
                                        <td class="py-4 px-4 whitespace-nowrap">
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
                                        <td class="py-4 px-4 text-right whitespace-nowrap">
                                            <div class="flex items-center justify-end gap-2">
                                                @php
                                                    $productModalData = [
                                                        'id' => $product->id,
                                                        'name' => $product->name,
                                                        'description' => $product->description,
                                                        'price' => $product->price,
                                                        'category_id' => $product->category_id,
                                                        'is_active' => $product->is_active,
                                                        'options' => $product->options ?? [],
                                                        'image_url' => $product->image ? asset('storage/' . $product->image) : null,
                                                        'update_url' => route('products.update', $product->id),
                                                    ];
                                                @endphp
                                                <button
                                                    type="button"
                                                    class="inline-flex items-center px-3 py-1.5 bg-simplicitea-50 dark:bg-simplicitea-900/30 text-simplicitea-700 dark:text-simplicitea-300 rounded-lg text-xs font-medium hover:bg-simplicitea-100 dark:hover:bg-simplicitea-900/50 transition-colors"
                                                    data-product='@json($productModalData)'
                                                    onclick="openEditProductModal(this)">
                                                    Edit
                                                </button>
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
                                    <tr id="liveSearchNoResults" class="hidden">
                                        <td colspan="5" class="py-10 px-4 text-center text-gray-500 dark:text-gray-400">
                                            No matching products found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div id="createProductModal" class="fixed inset-0 z-50 hidden">
        <div id="createProductBackdrop" class="absolute inset-0 bg-black/60"></div>
        <div class="relative h-full w-full flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 w-full max-w-full sm:max-w-2xl max-h-[90vh] overflow-y-auto hide-scrollbar rounded-3xl shadow-2xl border border-gray-200 dark:border-gray-700">
                <div class="sticky top-0 z-10 flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-simplicitea-600 via-simplicitea-500 to-teal-500 rounded-t-3xl">
                    <div>
                        <h3 id="productModalTitle" class="text-lg font-semibold text-black">Create Product</h3>
                        <p class="text-sm text-black/70 mt-0.5">Add or update product details and variants</p>
                    </div>
                    <button type="button" id="closeCreateProductModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="p-6">
                    @include('products.partials.form', ['categories' => $categories, 'isModal' => true, 'submitLabel' => 'Create Product'])
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const createModal = document.getElementById('createProductModal');
            const openCreateButton = document.getElementById('openCreateProductModal');
            const openCreateButtonEmpty = document.getElementById('openCreateProductModalEmpty');
            const closeCreateButton = document.getElementById('closeCreateProductModal');
            const createBackdrop = document.getElementById('createProductBackdrop');

            const modalTitle = document.getElementById('productModalTitle');

            window.openCreateProductModal = function () {
                if (!createModal) return;
                if (modalTitle) modalTitle.textContent = 'Create Product';
                if (window.productFormReset) {
                    window.productFormReset();
                }
                createModal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            };

            window.openEditProductModal = function (button) {
                if (!createModal || !button) return;
                const product = JSON.parse(button.dataset.product || '{}');
                if (modalTitle) modalTitle.textContent = 'Edit Product';
                if (window.productFormLoad) {
                    window.productFormLoad(product);
                }
                createModal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            };

            window.closeCreateProductModal = function () {
                if (!createModal) return;
                createModal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            };

            window.closeProductModal = window.closeCreateProductModal;

            if (openCreateButton) {
                openCreateButton.addEventListener('click', window.openCreateProductModal);
            }
            if (openCreateButtonEmpty) {
                openCreateButtonEmpty.addEventListener('click', window.openCreateProductModal);
            }
            if (closeCreateButton) {
                closeCreateButton.addEventListener('click', window.closeCreateProductModal);
            }
            if (createBackdrop) {
                createBackdrop.addEventListener('click', window.closeCreateProductModal);
            }

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    window.closeProductModal();
                }
            });

            @if($errors->any())
            window.openCreateProductModal();
            @endif

            const searchInput = document.getElementById('productSearchInput');
            const clearButton = document.getElementById('clearProductSearch');
            const tableBody = document.getElementById('productsTableBody');

            if (!searchInput || !tableBody) {
                return;
            }

            const rows = Array.from(tableBody.querySelectorAll('tr.product-row'));
            const noResultsRow = document.getElementById('liveSearchNoResults');

            const applyFilter = () => {
                const term = searchInput.value.trim().toLowerCase();
                let visibleCount = 0;

                rows.forEach((row) => {
                    const rowText = row.textContent.toLowerCase();
                    const isMatch = term === '' || rowText.includes(term);
                    row.classList.toggle('hidden', !isMatch);
                    if (isMatch) {
                        visibleCount += 1;
                    }
                });

                if (noResultsRow) {
                    noResultsRow.classList.toggle('hidden', visibleCount > 0);
                }

                if (clearButton) {
                    clearButton.classList.toggle('hidden', term === '');
                }
            };

            searchInput.addEventListener('input', applyFilter);

            if (clearButton) {
                clearButton.addEventListener('click', () => {
                    searchInput.value = '';
                    searchInput.focus();
                    applyFilter();
                });
            }

            applyFilter();
        });
    </script>
</x-app-layout>
