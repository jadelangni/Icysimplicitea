<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Point of Sale') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
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
                                     data-stock="{{ $product->inventory->first()->quantity ?? 0 }}">
                                    @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-20 object-cover rounded mb-2">
                                    @else
                                    <div class="w-full h-20 bg-gray-200 rounded mb-2 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    @endif
                                    <h4 class="font-medium text-sm text-gray-900 mb-1">{{ $product->name }}</h4>
                                    <p class="text-lg font-bold text-blue-600">₱{{ number_format($product->price, 2) }}</p>
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

        // Add to cart
        document.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('click', function() {
                const productId = this.dataset.productId;
                const productName = this.dataset.productName;
                const productPrice = parseFloat(this.dataset.productPrice);
                const stock = parseInt(this.dataset.stock);

                if (stock <= 0) {
                    alert('This product is out of stock!');
                    return;
                }

                // Check if product already in cart
                const existingItem = cart.find(item => item.productId === productId);
                if (existingItem) {
                    if (existingItem.quantity >= stock) {
                        alert('Cannot add more items than available in stock!');
                        return;
                    }
                    existingItem.quantity += 1;
                } else {
                    cart.push({
                        productId,
                        productName,
                        productPrice,
                        quantity: 1,
                        stock
                    });
                }

                updateCart();
            });
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
                    return `
                        <div class="bg-white border border-gray-200 rounded-lg p-3 mb-2">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="font-medium text-sm text-gray-900">${item.productName}</p>
                                    <p class="text-xs text-gray-500">₱${item.productPrice.toFixed(2)} each</p>
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
                quantity: item.quantity
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