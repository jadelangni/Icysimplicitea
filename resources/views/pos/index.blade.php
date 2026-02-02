<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Point of Sale') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    <span class="block sm:inline">{{ session('success') }}</span>
                    <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                        <svg onclick="this.parentElement.parentElement.style.display='none'" class="fill-current h-6 w-6 text-green-500 cursor-pointer" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                    </span>
                </div>
            @endif

            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Left Panel: Product Categories and Item List -->
                <div class="flex-1 lg:w-2/3">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Products</h3>
                            
                            <!-- Category Tabs -->
                            <div class="mb-6">
                                <h4 class="font-medium text-gray-900 mb-3">Product Categories</h4>
                                <div class="flex flex-wrap gap-2">
                                    <button class="category-tab active px-4 py-2 bg-simplicitea-600 text-white rounded-lg font-medium text-sm hover:bg-simplicitea-700 transition-colors" data-category="all">
                                        🔍 All Products
                                    </button>
                                    @foreach($categories as $category)
                                    <button class="category-tab px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium text-sm hover:bg-gray-200 transition-colors" data-category="{{ $category->id }}">
                                        {{ $category->name }}
                                    </button>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Products Grid -->
                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                                @foreach($products as $product)
                                <div class="product-card bg-gray-50 rounded-lg p-3 cursor-pointer hover:bg-gray-100 hover:shadow-md transition-all duration-200 border border-gray-200" 
                                     data-category="{{ $product->category_id }}"
                                     data-product-id="{{ $product->id }}"
                                     data-product-name="{{ $product->name }}"
                                     data-product-price="{{ $product->price }}"
                                     data-stock="{{ $product->inventory->first()->quantity ?? 0 }}"
                                     data-options='{{ json_encode($product->options ?? [], JSON_HEX_APOS | JSON_HEX_QUOT) }}'>
                                    @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-20 object-cover rounded mb-2">
                                    @else
                                    <div class="w-full h-20 bg-gray-200 rounded mb-2 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    @endif
                                    <h4 class="font-medium text-sm text-gray-900 mb-1 flex items-center">
                                        {{ $product->name }}
                                        @if($product->options && is_array($product->options) && !empty($product->options))
                                            <svg class="w-3 h-3 ml-1 text-simplicitea-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path>
                                            </svg>
                                        @endif
                                    </h4>
                                    @if($product->options && is_array($product->options))
                                        <div class="text-xs text-gray-600 mb-2">
                                            @foreach($product->options as $option)
                                                @if(isset($option['name']) && isset($option['values']) && is_array($option['values']))
                                                    <div class="mb-1">
                                                        <span class="font-medium">{{ $option['name'] }}:</span>
                                                        @foreach($option['values'] as $index => $value)
                                                            @if(isset($value['label']) && isset($value['price']))
                                                                <span class="text-simplicitea-600">{{ $value['label'] }} (₱{{ number_format($value['price'], 0) }}){{ $index < count($option['values']) - 1 ? ', ' : '' }}</span>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                    @if($product->options && is_array($product->options) && !empty($product->options))
                                        <p class="text-sm text-gray-500">Base: ₱{{ number_format($product->price, 2) }}</p>
                                    @else
                                        <p class="text-lg font-bold text-blue-600">₱{{ number_format($product->price, 2) }}</p>
                                    @endif
                                    @php $qty = $product->inventory->first()->quantity ?? 0; @endphp
                                    <p class="text-xs">
                                        @if($product->is_active && $qty > 0)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Available</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Unavailable</span>
                                        @endif
                                    </p>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Panel: Current Customer's Order Summary -->
                <div class="w-full lg:w-1/3 lg:min-w-[380px]">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Customer's Order</h3>
                            
                            <!-- Cart Items -->
                            <div id="cart-items" class="space-y-2 mb-6 min-h-[300px] max-h-[400px] overflow-y-auto bg-gray-50 rounded-lg p-4">
                                <div class="text-gray-500 text-center py-12">
                                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m0 0h7.5m-7.5 0H4"></path>
                                    </svg>
                                    <p class="text-sm">No items selected</p>
                                    <p class="text-xs text-gray-400">Click on products to add them</p>
                                </div>
                            </div>

                            <!-- Order Summary -->
                            <div class="bg-simplicitea-50 rounded-lg p-4 mb-6">
                                <h4 class="font-medium text-gray-900 mb-3">Order Summary</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Subtotal:</span>
                                        <span id="subtotal" class="font-medium">₱0.00</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Tax (0%):</span>
                                        <span id="tax" class="font-medium">₱0.00</span>
                                    </div>
                                    <div class="border-t border-gray-200 pt-2">
                                        <div class="flex justify-between text-lg font-bold text-simplicitea-700">
                                            <span>Total:</span>
                                            <span id="total">₱0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Section -->
                            <form id="checkout-form" class="space-y-4">
                                @csrf
                                <div class="bg-white rounded-lg border border-gray-200 p-4">
                                    <h4 class="font-medium text-gray-900 mb-3">Payment Details</h4>
                                    
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                                            <select name="payment_method" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500" required>
                                                <option value="cash">💵 Cash</option>
                                                <option value="card">💳 Card</option>
                                                <option value="gcash">📱 GCash</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Amount Paid</label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">₱</span>
                                                <input type="number" name="amount_paid" step="0.01" min="0" placeholder="0.00" class="w-full pl-8 border-gray-300 rounded-lg shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <button type="submit" id="process-sale-btn" class="w-full bg-simplicitea-600 text-white py-3 px-4 rounded-lg hover:bg-simplicitea-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium transition-colors" disabled>
                                        🛒 Process Sale
                                    </button>
                                    
                                    <button type="button" id="clear-cart-btn" class="w-full bg-gray-500 text-white py-2 px-4 rounded-lg hover:bg-gray-600 transition-colors">
                                        🗑️ Clear Cart
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for POS functionality -->
    <!-- Product Options Modal -->
    <div id="product-options-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <div id="product-options-backdrop" class="absolute inset-0 bg-black opacity-50 z-40"></div>
        <div class="bg-white rounded-lg shadow-lg relative z-50 w-full max-w-lg p-6 pointer-events-auto">
            <h3 id="modal-product-name" class="text-lg font-medium text-gray-900 mb-4">Choose Options</h3>
            <form id="product-options-form" class="space-y-4">
                <div id="options-container" class="space-y-3"></div>
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" id="modal-cancel-btn" class="px-4 py-2 bg-gray-200 rounded">Cancel</button>
                    <button type="button" id="modal-add-btn" class="px-4 py-2 bg-simplicitea-600 text-white rounded">Add to Cart</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        let cart = [];
        let subtotal = 0;

        // Category filtering
        document.querySelectorAll('.category-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Update active tab
                document.querySelectorAll('.category-tab').forEach(t => {
                    t.classList.remove('active', 'bg-simplicitea-600', 'text-white');
                    t.classList.add('bg-gray-100', 'text-gray-700');
                });
                this.classList.add('active', 'bg-simplicitea-600', 'text-white');
                this.classList.remove('bg-gray-100', 'text-gray-700');

                // Filter products
                const category = this.dataset.category;
                document.querySelectorAll('.product-card').forEach(card => {
                    if (category === 'all' || card.dataset.category === category) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });

        // Add to cart (supports product options/variants)
        document.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('click', function() {
                const productId = this.dataset.productId;
                const productName = this.dataset.productName;
                const productPrice = parseFloat(this.dataset.productPrice);
                const stock = parseInt(this.dataset.stock);
                const optionsData = this.getAttribute('data-options') || '[]';
                let options = [];
                try { 
                    options = JSON.parse(optionsData); 
                    if (!Array.isArray(options)) options = [];
                } catch(e) { 
                    console.error('Error parsing options for product:', productName, e);
                    options = []; 
                }

                if (stock <= 0) {
                    alert('This product is out of stock!');
                    return;
                }

                // If product has options, open modal to select
                if (options && options.length > 0) {
                    openOptionsModal({ productId, productName, productPrice, stock, options });
                    return;
                }

                // No options - default add behavior
                addOrIncrementCartItem({ productId, productName, productPrice, stock, options: null });
                updateCart();
            });
        });

        // Helper to find existing cart item by productId + options
        function sameOptions(a, b) {
            try { return JSON.stringify(a || {}) === JSON.stringify(b || {}); }
            catch(e) { return false; }
        }

        function addOrIncrementCartItem(item) {

            const existingItem = cart.find(i => i.productId === item.productId && sameOptions(i.options, item.options));
            if (existingItem) {
                if (existingItem.quantity >= item.stock) {
                    alert('Cannot add more items than available in stock!');
                    return;
                }
                // Update price in case it changed
                existingItem.productPrice = item.productPrice;
                existingItem.quantity += 1;

            } else {
                cart.push({
                    productId: item.productId,
                    productName: item.productName,
                    productPrice: item.productPrice,
                    quantity: 1,
                    stock: item.stock,
                    options: item.options || null
                });

            }
        }

        // Open modal to select product options
        function openOptionsModal(product) {
            const modal = document.getElementById('product-options-modal');
            const container = document.getElementById('options-container');
            const title = document.getElementById('modal-product-name');
            container.innerHTML = '';
            title.textContent = product.productName;
            
            // Debug: log options to console


            // Build selects for each option
            product.options.forEach((opt, idx) => {
                const id = `opt-${idx}`;
                let values = opt.values || [];
                // Ensure values is always an array
                if (!Array.isArray(values)) {
                    values = typeof values === 'string' ? values.split(',').map(v => v.trim()) : [values];
                }
                

                
                const wrapper = document.createElement('div');
                wrapper.className = 'space-y-1';

                const label = document.createElement('label');
                label.className = 'block text-sm font-medium text-gray-700';
                label.textContent = opt.name || `Option ${idx+1}`;

                const select = document.createElement('select');
                select.className = 'w-full border-gray-300 rounded-lg shadow-sm';
                select.setAttribute('data-option-name', opt.name || `Option ${idx+1}`);

                // Add values (support value objects with price modifiers)
                values.forEach((v, vIdx) => {

                    const optionEl = document.createElement('option');
                    if (v && typeof v === 'object' && (v.label || v.value || v.name)) {
                        const labelValue = v.label || v.value || v.name || '';
                        optionEl.value = labelValue;
                        const modifier = (v.price !== undefined && v.price !== null) ? parseFloat(v.price) : 0;
                        const modText = modifier > 0 ? ` (+₱${modifier.toFixed(2)})` : modifier < 0 ? ` (-₱${Math.abs(modifier).toFixed(2)})` : '';
                        optionEl.textContent = labelValue + modText;
                        if (v.price !== undefined && v.price !== null) {
                            optionEl.dataset.price = parseFloat(v.price);

                        }
                    } else {
                        // Handle string values or simple values
                        const strValue = String(v);
                        optionEl.value = strValue;
                        optionEl.textContent = strValue;

                    }
                    select.appendChild(optionEl);
                });

                // Price hint element under select
                const priceHint = document.createElement('div');
                priceHint.className = 'text-xs text-gray-500 mt-1';
                priceHint.textContent = '';

                wrapper.appendChild(label);
                wrapper.appendChild(select);
                wrapper.appendChild(priceHint);
                container.appendChild(wrapper);
            });

            // Show modal
            modal.classList.remove('hidden');

            // Handlers are attached globally; just show modal and populate selects
            // Store current product info on modal dataset for global handlers
            modal.dataset.currentProduct = JSON.stringify(product);

            // Update price hint when selects change
            const updateModalPrice = () => {
                const selects = container.querySelectorAll('select');
                let computed = parseFloat(product.productPrice); // fallback to base price
                
                // For fixed pricing, use the price from the selected option (typically Size)
                selects.forEach(s => {
                    const sel = s.options[s.selectedIndex];
                    const p = sel && sel.dataset && sel.dataset.price ? sel.dataset.price : null;
                    if (p && !isNaN(parseFloat(p)) && parseFloat(p) > 0) {
                        computed = parseFloat(p); // Use fixed price from option
                        return; // Use first valid fixed price found
                    }
                });
                // update each price hint for selects
                container.querySelectorAll('select').forEach(s => {
                    const hint = s.parentElement.querySelector('.text-xs');
                    const sel = s.options[s.selectedIndex];
                    if (sel && sel.dataset && sel.dataset.price !== undefined) {
                        const price = parseFloat(sel.dataset.price);
                        if (price > 0) {
                            hint.textContent = `Price: ₱${price.toFixed(2)}`;
                        } else {
                            hint.textContent = `Uses base price: ₱${parseFloat(product.productPrice).toFixed(2)}`;
                        }
                    } else {
                        hint.textContent = `Uses base price: ₱${parseFloat(product.productPrice).toFixed(2)}`;
                    }
                });
                // Show total computed price
                let totalHint = container.querySelector('.total-price-hint');
                if (!totalHint) {
                    totalHint = document.createElement('div');
                    totalHint.className = 'total-price-hint text-sm font-medium text-simplicitea-600 mt-2 p-2 bg-simplicitea-50 rounded';
                    container.appendChild(totalHint);
                }
                totalHint.textContent = `Total Price: ₱${computed.toFixed(2)}`;

                // store computed price on modal dataset for add handler to read
                modal.dataset.computedPrice = computed;
            };

            container.querySelectorAll('select').forEach(s => s.addEventListener('change', updateModalPrice));

            // initialize price hints
            updateModalPrice();
        }

        // Close modal when clicking backdrop
        document.getElementById('product-options-backdrop').addEventListener('click', function() {
            document.getElementById('product-options-modal').classList.add('hidden');
        });

        // Global cancel button handler
        document.getElementById('modal-cancel-btn').addEventListener('click', function() {
            document.getElementById('product-options-modal').classList.add('hidden');
        });

        // Global add button handler
        document.getElementById('modal-add-btn').addEventListener('click', function() {
            const modal = document.getElementById('product-options-modal');
            const container = document.getElementById('options-container');
            const product = modal.dataset.currentProduct ? JSON.parse(modal.dataset.currentProduct) : null;
            if (!product) {
                alert('No product selected');
                return;
            }
            const selects = container.querySelectorAll('select');
            const selected = {};
            selects.forEach(s => {
                const name = s.getAttribute('data-option-name') || s.name;
                selected[name] = s.value;
            });

            // Get computed price from modal dataset or compute it again
            let computedPrice = modal.dataset.computedPrice ? parseFloat(modal.dataset.computedPrice) : parseFloat(product.productPrice);
            
            // Double-check computation using fixed pricing
            computedPrice = parseFloat(product.productPrice); // fallback to base price
            selects.forEach(s => {
                const sel = s.options[s.selectedIndex];
                const price = sel && sel.dataset && sel.dataset.price ? parseFloat(sel.dataset.price) : 0;
                if (!isNaN(price) && price > 0) {
                    computedPrice = price; // Use fixed price from option
                    return; // Use first valid fixed price found
                }
            });
            

            
            addOrIncrementCartItem({
                productId: product.productId,
                productName: product.productName,
                productPrice: computedPrice,
                stock: product.stock,
                options: selected
            });

            modal.classList.add('hidden');
            updateCart();
        });

        // Update cart display
        function updateCart() {
            const cartContainer = document.getElementById('cart-items');
            subtotal = 0;

            if (cart.length === 0) {
                cartContainer.innerHTML = `
                    <div class="text-gray-500 text-center py-12">
                        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m0 0h7.5m-7.5 0H4"></path>
                        </svg>
                        <p class="text-sm">No items selected</p>
                        <p class="text-xs text-gray-400">Click on products to add them</p>
                    </div>
                `;
            } else {
                cartContainer.innerHTML = cart.map((item, index) => {
                    const itemTotal = item.productPrice * item.quantity;
                    subtotal += itemTotal;
                    const optionsHtml = item.options ? Object.keys(item.options).map(k => `<div class="text-xs text-gray-500">${k}: ${item.options[k]}</div>`).join('') : '';
                    return `
                        <div class="bg-white border border-gray-200 rounded-lg p-3 mb-2">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="font-medium text-sm text-gray-900">${item.productName}</p>
                                    <p class="text-xs text-gray-500">₱${item.productPrice.toFixed(2)} each</p>
                                    ${optionsHtml}
                                </div>
                                <button onclick="removeItem(${index})" class="text-red-500 hover:text-red-700 text-lg leading-none" title="Remove item">
                                    ×
                                </button>
                            </div>
                            <div class="flex items-center justify-between mt-3">
                                <div class="flex items-center space-x-2">
                                    <button onclick="updateQuantity(${index}, -1)" class="w-7 h-7 bg-gray-100 hover:bg-gray-200 rounded-full flex items-center justify-center text-sm font-medium">-</button>
                                    <span class="w-8 text-center font-medium">${item.quantity}</span>
                                    <button onclick="updateQuantity(${index}, 1)" class="w-7 h-7 bg-gray-100 hover:bg-gray-200 rounded-full flex items-center justify-center text-sm font-medium">+</button>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-simplicitea-600">₱${itemTotal.toFixed(2)}</p>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');
            }

            // Update totals
            const tax = 0; // You can implement tax calculation here
            const total = subtotal + tax;
            
            document.getElementById('subtotal').textContent = '₱' + subtotal.toFixed(2);
            document.getElementById('tax').textContent = '₱' + tax.toFixed(2);
            document.getElementById('total').textContent = '₱' + total.toFixed(2);

            // Enable/disable checkout button
            document.getElementById('process-sale-btn').disabled = cart.length === 0;
        }

        // Update quantity
        function updateQuantity(index, change) {
            const item = cart[index];
            const newQuantity = item.quantity + change;
            
            if (newQuantity <= 0) {
                cart.splice(index, 1);
            } else if (newQuantity <= item.stock) {
                item.quantity = newQuantity;
            } else {
                alert('Cannot add more items than available in stock!');
                return;
            }
            
            updateCart();
        }

        // Remove item
        function removeItem(index) {
            cart.splice(index, 1);
            updateCart();
        }

        // Clear cart
        document.getElementById('clear-cart-btn').addEventListener('click', function() {
            if (confirm('Are you sure you want to clear the cart?')) {
                cart = [];
                updateCart();
            }
        });

        // Process sale
        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (cart.length === 0) {
                alert('Cart is empty!');
                return;
            }

            const formData = new FormData(this);
            const items = cart.map(item => ({
                product_id: item.productId,
                quantity: item.quantity,
                options: item.options || null
            }));

            // Add cart items to form data
            formData.append('items', JSON.stringify(items));

            // Submit to server
            fetch('{{ route("pos.process-sale") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('[name=_token]').value
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect_url;
                } else {
                    alert(data.message || 'An error occurred');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while processing the sale');
            });
        });
    </script>
</x-app-layout>