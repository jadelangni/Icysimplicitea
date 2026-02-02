<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-gray-600">Welcome to Icy's Simplicitea POS System</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Today's Sales -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Today's Sales</p>
                        <p class="text-2xl font-bold text-gray-900">₱{{ number_format($todaysSales ?? 0, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Total Transactions -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Today's Transactions</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $todaysTransactions ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Low Stock Items -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Low Stock Alert</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $lowStockCount ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Active Products -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Active Products</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $activeProducts ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Daily Sales Chart -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Daily Sales</h3>
                    <p class="text-sm text-gray-500">Sales overview for the past 7 days</p>
                </div>
                <div class="p-6">
                    <canvas id="dailySalesChart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <a href="{{ route('pos.index') }}" class="flex flex-col items-center p-4 bg-simplicitea-50 rounded-lg hover:bg-simplicitea-100 transition-colors">
                            <svg class="w-8 h-8 text-simplicitea-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m0 0h7.5m-7.5 0H4"></path>
                            </svg>
                            <span class="text-sm font-medium text-simplicitea-900">New Sale</span>
                        </a>

                        @if(auth()->user()->isOwner() || auth()->user()->isSupervisor())
                        <a href="{{ route('products.create') }}" class="flex flex-col items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                            <svg class="w-8 h-8 text-green-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            <span class="text-sm font-medium text-green-900">Add Product</span>
                        </a>

                        <a href="{{ route('inventory.index') }}" class="flex flex-col items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors">
                            <svg class="w-8 h-8 text-yellow-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            <span class="text-sm font-medium text-yellow-900">Manage Inventory</span>
                        </a>

                        <a href="{{ route('reports.index') }}" class="flex flex-col items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                            <svg class="w-8 h-8 text-purple-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <span class="text-sm font-medium text-purple-900">View Reports</span>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Sales -->
        <div class="mt-6 bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Recent Sales</h3>
                    <div class="flex items-center text-sm text-gray-500">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Last updated: <span id="lastUpdated">{{ now()->format('h:i A') }}</span>
                    </div>
                </div>
            </div>
            <div class="p-6">
                @if(isset($recentSales) && $recentSales->count() > 0)
                    <div class="overflow-hidden">
                        <div class="space-y-3">
                            @foreach($recentSales as $index => $sale)
                            <div class="flex items-center justify-between p-4 {{ $index === 0 ? 'bg-simplicitea-50 border-2 border-simplicitea-200' : 'bg-gray-50 border border-gray-200' }} rounded-lg hover:shadow-md transition-shadow">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 {{ $index === 0 ? 'bg-simplicitea-100' : 'bg-gray-200' }} rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 {{ $index === 0 ? 'text-simplicitea-600' : 'text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex items-center space-x-2">
                                            <p class="text-sm font-semibold text-gray-900">{{ $sale->receipt_number }}</p>
                                            @if($index === 0)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Latest
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-500">
                                            By {{ $sale->user->name ?? 'Staff' }} • 
                                            {{ $sale->created_at->diffForHumans() }}
                                        </p>
                                        <p class="text-xs text-gray-400">
                                            {{ $sale->created_at->format('M d, Y h:i A') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-gray-900">₱{{ number_format($sale->total_amount, 2) }}</p>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-xs px-2 py-1 {{ $sale->payment_method === 'cash' ? 'bg-green-100 text-green-800' : ($sale->payment_method === 'card' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800') }} rounded-full">
                                            {{ ucfirst($sale->payment_method) }}
                                        </span>
                                        <a href="{{ route('pos.receipt', $sale->id) }}" class="text-xs text-simplicitea-600 hover:text-simplicitea-800">
                                            View →
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="mt-4 text-center">
                            <p class="text-sm text-gray-500">
                                Showing {{ $recentSales->count() }} of {{ $todaysTransactions }} today's transactions
                            </p>
                        </div>
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        <p class="text-gray-500 text-lg mb-2">No recent sales found</p>
                        <p class="text-gray-400 text-sm">Sales will appear here once you start processing orders</p>
                        <div class="mt-4">
                            <a href="{{ route('pos.index') }}" class="inline-flex items-center px-4 py-2 bg-simplicitea-600 text-white rounded-md hover:bg-simplicitea-700">
                                Start Selling
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Daily Sales Chart
    const ctx = document.getElementById('dailySalesChart').getContext('2d');
    const dailySalesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($dailyLabels ?? []) !!},
            datasets: [{
                label: 'Sales (₱)',
                data: {!! json_encode($dailySales ?? []) !!},
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(59, 130, 246, 0.7)',
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(59, 130, 246, 0.9)',
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(59, 130, 246, 0.7)',
                    'rgba(59, 130, 246, 0.6)'
                ],
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 1,
                borderRadius: 4,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            elements: {
                bar: {
                    borderRadius: 4
                }
            }
        }
    });

    // Auto-refresh functionality for recent sales
    document.addEventListener('DOMContentLoaded', function() {
        // Check if we're coming from a successful POS transaction
        const urlParams = new URLSearchParams(window.location.search);
        const fromPOS = urlParams.get('from_pos');
        const success = urlParams.get('success');
        
        if (fromPOS === '1' && success === '1') {
            // Show success notification
            showSuccessNotification('Sale processed successfully! Recent sales updated.');
            
            // Update the last updated timestamp
            document.getElementById('lastUpdated').textContent = new Date().toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
            
            // Clean up URL parameters
            const cleanURL = window.location.pathname;
            window.history.replaceState({}, document.title, cleanURL);
            
            // Scroll to recent sales section
            setTimeout(() => {
                const headings = document.querySelectorAll('h3');
                let recentSalesSection = null;
                headings.forEach(h3 => {
                    if (h3.textContent.includes('Recent Sales')) {
                        recentSalesSection = h3.closest('.bg-white');
                    }
                });
                if (recentSalesSection) {
                    recentSalesSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }, 500);
        }
        
        // Auto-refresh recent sales every 30 seconds when dashboard is active
        let refreshInterval;
        
        function startAutoRefresh() {
            refreshInterval = setInterval(() => {
                if (document.visibilityState === 'visible') {
                    refreshRecentSales();
                }
            }, 30000); // 30 seconds
        }
        
        function stopAutoRefresh() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
        }
        
        // Start auto-refresh when page loads
        startAutoRefresh();
        
        // Stop auto-refresh when page is hidden, restart when visible
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'hidden') {
                stopAutoRefresh();
            } else {
                startAutoRefresh();
            }
        });
    });

    // Function to refresh recent sales section via AJAX
    async function refreshRecentSales() {
        try {
            const response = await fetch('{{ route("dashboard.recent-sales") }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                updateRecentSalesDisplay(data.recentSales, data.todaysTransactions);
                
                // Update timestamp
                document.getElementById('lastUpdated').textContent = new Date().toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                });
            }
        } catch (error) {
            console.log('Auto-refresh error:', error);
        }
    }

    // Function to update the recent sales display
    function updateRecentSalesDisplay(recentSales, todaysTransactions) {
        const container = document.querySelector('.space-y-3');
        if (!container || !recentSales) return;
        
        if (recentSales.length === 0) {
            // Show no sales message
            container.innerHTML = `
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    <p class="text-gray-500 text-lg mb-2">No recent sales found</p>
                    <p class="text-gray-400 text-sm">Sales will appear here once you start processing orders</p>
                    <div class="mt-4">
                        <a href="{{ route('pos.index') }}" class="inline-flex items-center px-4 py-2 bg-simplicitea-600 text-white rounded-md hover:bg-simplicitea-700">
                            Start Selling
                        </a>
                    </div>
                </div>
            `;
            return;
        }
        
        // Generate HTML for recent sales
        let salesHTML = '';
        recentSales.forEach((sale, index) => {
            const isLatest = index === 0;
            const paymentMethodColors = {
                cash: 'bg-green-100 text-green-800',
                card: 'bg-blue-100 text-blue-800',
                default: 'bg-purple-100 text-purple-800'
            };
            const paymentColor = paymentMethodColors[sale.payment_method] || paymentMethodColors.default;
            
            salesHTML += `
                <div class="flex items-center justify-between p-4 ${isLatest ? 'bg-simplicitea-50 border-2 border-simplicitea-200' : 'bg-gray-50 border border-gray-200'} rounded-lg hover:shadow-md transition-shadow">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 ${isLatest ? 'bg-simplicitea-100' : 'bg-gray-200'} rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 ${isLatest ? 'text-simplicitea-600' : 'text-gray-600'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center space-x-2">
                                <p class="text-sm font-semibold text-gray-900">${sale.receipt_number}</p>
                                ${isLatest ? '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Latest</span>' : ''}
                            </div>
                            <p class="text-sm text-gray-500">
                                By ${sale.user?.name || 'Staff'} • ${formatTimeAgo(sale.created_at)}
                            </p>
                            <p class="text-xs text-gray-400">
                                ${formatDateTime(sale.created_at)}
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-gray-900">₱${parseFloat(sale.total_amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs px-2 py-1 ${paymentColor} rounded-full">
                                ${sale.payment_method.charAt(0).toUpperCase() + sale.payment_method.slice(1)}
                            </span>
                            <a href="/pos/receipt/${sale.id}" class="text-xs text-simplicitea-600 hover:text-simplicitea-800">
                                View →
                            </a>
                        </div>
                    </div>
                </div>
            `;
        });
        
        salesHTML += `
            <div class="mt-4 text-center">
                <p class="text-sm text-gray-500">
                    Showing ${recentSales.length} of ${todaysTransactions} today's transactions
                </p>
            </div>
        `;
        
        container.innerHTML = salesHTML;
    }

    // Helper functions for date formatting
    function formatTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffInMinutes = Math.floor((now - date) / (1000 * 60));
        
        if (diffInMinutes < 1) return 'Just now';
        if (diffInMinutes < 60) return `${diffInMinutes}m ago`;
        if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)}h ago`;
        return `${Math.floor(diffInMinutes / 1440)}d ago`;
    }
    
    function formatDateTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric', 
            year: 'numeric' 
        }) + ' ' + date.toLocaleTimeString('en-US', { 
            hour: 'numeric', 
            minute: '2-digit', 
            hour12: true 
        });
    }

    // Success notification function
    function showSuccessNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300';
        notification.innerHTML = `
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Animate out after 4 seconds
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 4000);
    }
</script>

</x-app-layout>
