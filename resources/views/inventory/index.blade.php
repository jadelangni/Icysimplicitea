<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Inventory') }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="mb-6 grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
                <!-- Left: Page Title + Search -->
                <div class="lg:col-span-2 bg-white p-4 rounded-lg shadow sm:flex sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-2xl font-semibold text-gray-900">Ingredients Inventory</h3>
                        <p class="text-sm text-gray-500">Manage ingredient stock levels for product preparation.</p>
                    </div>

                    <form method="GET" action="" class="mt-4 sm:mt-0 sm:ml-4">
                        <label for="q" class="sr-only">Search</label>
                        <div class="relative text-gray-400 focus-within:text-gray-600">
                            <input id="q" name="q" value="{{ request('q') }}" class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-md bg-white placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-simplicitea-500 focus:border-simplicitea-500 sm:text-sm" placeholder="Search items..." />
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                                </svg>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Summary Cards -->
                <div class="bg-white p-4 rounded-lg shadow">
                    <div class="text-sm text-gray-500">Total Ingredients</div>
                    <div class="mt-2 text-2xl font-semibold text-gray-900">{{ $ingredients->count() }}</div>
                </div>

                <div class="bg-white p-4 rounded-lg shadow">
                    <div class="text-sm text-gray-500">Low Stock Alerts</div>
                    @php
                        $lowStockCount = $ingredients->filter(function($i){ return $i->quantity <= $i->min_stock_level; })->count();
                    @endphp
                    <div class="mt-2 text-2xl font-semibold text-gray-900">{{ $lowStockCount }}</div>
                </div>

                <div class="bg-white p-4 rounded-lg shadow hidden lg:block">
                    <div class="text-sm text-gray-500">Quick Actions</div>
                    <div class="mt-3 space-y-2">
                        <a href="{{ route('inventory.create') }}" class="inline-flex items-center px-3 py-2 bg-simplicitea-600 text-white rounded-md text-sm hover:bg-simplicitea-700">Add Ingredient</a>

                        @if(Route::has('categories.index'))
                            <a href="{{ route('categories.index') }}" class="inline-flex items-center px-3 py-2 border border-gray-200 rounded-md text-sm hover:bg-gray-50">Add Category</a>
                        @else
                            <button class="inline-flex items-center px-3 py-2 border border-gray-200 rounded-md text-sm text-gray-400" disabled>Add Category</button>
                        @endif

                        @if(Route::has('reports.index'))
                            <a href="{{ route('reports.index') }}" class="inline-flex items-center px-3 py-2 border border-gray-200 rounded-md text-sm hover:bg-gray-50">Generate Report</a>
                        @else
                            <button class="inline-flex items-center px-3 py-2 border border-gray-200 rounded-md text-sm text-gray-400" disabled>Generate Report</button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    {{-- Top area: Recent Activities + Table --}}
                    <div class="mb-6 grid grid-cols-1 lg:grid-cols-5 gap-6">
                        <div class="lg:col-span-4">
                            <h4 class="text-lg font-medium text-gray-900 mb-3">Ingredients</h4>
                            @if($ingredients->isEmpty())
                                <div class="text-center py-16 text-gray-500">No ingredients found. <a href="{{ route('inventory.create') }}" class="text-simplicitea-600 hover:text-simplicitea-800">Add your first ingredient</a></div>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($ingredients as $ingredient)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm font-medium text-gray-900">{{ $ingredient->name }}</div>
                                                        <div class="text-sm text-gray-500">{{ $ingredient->description }}</div>
                                                    </td>
                                                    <td class="px-4 py-4 whitespace-nowrap text-sm">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $ingredient->status_color }}">
                                                            {{ $ingredient->status }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $ingredient->updated_at->format('Y-m-d H:i') }}
                                                    </td>
                                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">
                                                        <div class="font-medium">{{ number_format($ingredient->quantity, 1) }} {{ $ingredient->unit }}</div>
                                                        @if($ingredient->min_stock_level > 0)
                                                            <div class="text-xs text-gray-500">Min: {{ number_format($ingredient->min_stock_level, 1) }} {{ $ingredient->unit }}</div>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                        <a href="{{ route('inventory.edit', $ingredient) }}" class="text-simplicitea-600 hover:text-simplicitea-800 mr-3">Edit</a>
                                                        <form action="{{ route('inventory.destroy', $ingredient) }}" method="POST" class="inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('Delete ingredient?')">Delete</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        <div class="hidden lg:block bg-white p-4 rounded-lg shadow lg:col-span-1">
                            <h5 class="text-sm font-medium text-gray-900">Recent Updates</h5>
                            <div class="mt-3 space-y-3 text-sm text-gray-600">
                                @php
                                    $recent = $ingredients->sortByDesc('updated_at')->take(4);
                                @endphp
                                @forelse($recent as $ingredient)
                                    <div class="flex items-start">
                                        <span class="flex-shrink-0 mr-2 text-gray-400">•</span>
                                        <div>
                                            <div class="text-gray-800">Updated &ldquo;{{ $ingredient->name }}&rdquo;</div>
                                            <div class="text-xs text-gray-500">{{ $ingredient->updated_at->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-gray-500">No recent activity</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Pagination / Count --}}
                    <div class="mt-6 flex items-center justify-between">
                        <div class="text-sm text-gray-500">Showing {{ $ingredients->count() }} ingredients</div>
                        <div>
                            @if(method_exists($ingredients, 'links'))
                                {{ $ingredients->links() }}
                            @endif
                        </div>
                    </div>

                    {{-- Chart area (if present) --}}
                    @if(isset($chartLabels) && $chartLabels->isNotEmpty())
                        <div class="mt-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Sales in last 30 days</h3>
                            <div class="w-full" style="height:280px">
                                <canvas id="salesChart" class="w-full h-full"></canvas>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@if(isset($chartLabels) && $chartLabels->isNotEmpty())
    {{-- Chart.js CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (function() {
            const labels = {!! json_encode($chartLabels) !!};
            const data = {!! json_encode($chartData) !!};

            const ctx = document.getElementById('salesChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Quantity sold',
                        data: data,
                        backgroundColor: 'rgba(34,197,94,0.7)',
                        borderColor: 'rgba(34,197,94,1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        })();
    </script>
@endif
</x-app-layout>
