<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-black leading-tight">
                {{ __('Cashier Sales List') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-black">Filters</h3>
                </div>
                <form id="cashierFiltersForm" method="GET" action="{{ route('activity-logs.index') }}" class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label for="branch_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Branch</label>
                            <select name="branch_id" id="branch_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500 text-sm">
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                            <select name="role" id="role" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500 text-sm">
                                <option value="">All Roles</option>
                                <option value="cashier" {{ request('role') == 'cashier' ? 'selected' : '' }}>Cashier</option>
                                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                        </div>

                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date</label>
                            <input type="date" name="start_date" id="start_date" 
                                value="{{ request('start_date', now()->setTimezone('Asia/Manila')->format('Y-m-d')) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500 text-sm">
                        </div>

                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Date</label>
                            <input type="date" name="end_date" id="end_date" 
                                value="{{ request('end_date', now()->setTimezone('Asia/Manila')->format('Y-m-d')) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500 text-sm">
                        </div>

                    </div>
                </form>
            </div>

            <!-- Cashier List Table -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    User Name
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Role
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Branch
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Date Range
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($cashiers as $cashier)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-simplicitea-100 dark:bg-simplicitea-900 flex items-center justify-center">
                                                    <span class="text-simplicitea-600 dark:text-simplicitea-400 font-medium">
                                                        {{ strtoupper(substr($cashier->name, 0, 1)) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-black">
                                                    {{ $cashier->name }}
                                                </div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $cashier->email }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $cashier->role === 'admin' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' }}">
                                            {{ ucfirst($cashier->role) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $cashier->branch->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $startDate }} - {{ $endDate }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <button type="button"
                                            onclick="openDetailsModal({{ $cashier->id }}, '{{ addslashes($cashier->name) }}')"
                                            class="inline-flex items-center px-3 py-1.5 bg-simplicitea-600 hover:bg-simplicitea-700 text-black text-xs font-medium rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-simplicitea-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Details
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-black">No cashiers found</h3>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No active staff members match the current filters.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($cashiers->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $cashiers->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Sales Details Modal -->
    <div id="salesDetailsModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-80 transition-opacity" onclick="closeDetailsModal()"></div>

            <!-- Modal Panel -->
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-4xl w-full mx-4 overflow-hidden transform transition-all">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-simplicitea-600 to-simplicitea-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 id="modalTitle" class="text-lg font-semibold text-black">Cashier Sales Details</h3>
                            <p id="modalSubtitle" class="text-sm text-simplicitea-100 mt-0.5"></p>
                        </div>
                        <button type="button" onclick="closeDetailsModal()" class="text-black hover:text-gray-200 transition-colors">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Date Filter -->
                <div class="px-6 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex flex-wrap items-center gap-3">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">Filter by Date:</label>
                        <div class="flex items-center gap-2">
                            <input type="date" id="modalStartDate"
                                class="block w-40 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500 text-sm"
                                onchange="fetchSalesDetails()">
                            <span class="text-gray-500 dark:text-gray-400">to</span>
                            <input type="date" id="modalEndDate"
                                class="block w-40 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500 text-sm"
                                onchange="fetchSalesDetails()">
                        </div>
                        <button type="button" onclick="resetDateFilter()" class="text-sm text-simplicitea-600 hover:text-simplicitea-800 dark:text-simplicitea-400 dark:hover:text-simplicitea-300 whitespace-nowrap">
                            Reset to Today
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-4 max-h-[60vh] overflow-y-auto">
                    <!-- Loading State -->
                    <div id="modalLoading" class="hidden py-12 text-center">
                        <svg class="animate-spin h-10 w-10 mx-auto text-simplicitea-500" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Loading sales data...</p>
                    </div>

                    <!-- Error State -->
                    <div id="modalError" class="hidden py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <p id="modalErrorText" class="mt-3 text-sm text-red-500"></p>
                    </div>

                    <!-- Sales Items Table -->
                    <div id="modalContent" class="hidden">
                        <!-- Summary Cards -->
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="bg-blue-50 dark:bg-blue-900/30 rounded-lg p-3">
                                <p class="text-xs font-medium text-blue-600 dark:text-blue-400 uppercase">Total Transactions</p>
                                <p id="totalTransactions" class="text-xl font-bold text-blue-700 dark:text-blue-300 mt-1">0</p>
                            </div>
                            <div class="bg-green-50 dark:bg-green-900/30 rounded-lg p-3">
                                <p class="text-xs font-medium text-green-600 dark:text-green-400 uppercase">Grand Total</p>
                                <p id="grandTotal" class="text-xl font-bold text-green-700 dark:text-green-300 mt-1">₱0.00</p>
                            </div>
                        </div>

                        <div id="salesGroupedBody">
                            <!-- Populated via JavaScript -->
                        </div>

                        <!-- Empty State (inside content) -->
                        <div id="noSalesMessage" class="hidden py-8 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <h4 class="mt-2 text-sm font-medium text-gray-900 dark:text-black">No sales found</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This cashier has no sales in the selected date range.</p>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-3 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                    <button type="button" onclick="closeDetailsModal()"
                        class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 text-sm transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentUserId = null;
        let currentUserName = '';
        const pageStartDate = '{{ $filterStartDate }}';
        const pageEndDate = '{{ $filterEndDate }}';

        function openDetailsModal(userId, userName) {
            currentUserId = userId;
            currentUserName = userName;

            // Use the page-level date filter values
            document.getElementById('modalStartDate').value = pageStartDate;
            document.getElementById('modalEndDate').value = pageEndDate;
            document.getElementById('modalTitle').textContent = `Sales Details - ${userName}`;

            // Show modal
            document.getElementById('salesDetailsModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            // Fetch data
            fetchSalesDetails();
        }

        function closeDetailsModal() {
            document.getElementById('salesDetailsModal').classList.add('hidden');
            document.body.style.overflow = '';
            currentUserId = null;
        }

        function resetDateFilter() {
            const phNow = new Date(new Date().toLocaleString('en-US', { timeZone: 'Asia/Manila' }));
            const today = phNow.getFullYear() + '-' +
                String(phNow.getMonth() + 1).padStart(2, '0') + '-' +
                String(phNow.getDate()).padStart(2, '0');
            document.getElementById('modalStartDate').value = today;
            document.getElementById('modalEndDate').value = today;
            fetchSalesDetails();
        }

        async function fetchSalesDetails() {
            if (!currentUserId) return;

            const startDate = document.getElementById('modalStartDate').value;
            const endDate = document.getElementById('modalEndDate').value;

            // Show loading
            document.getElementById('modalLoading').classList.remove('hidden');
            document.getElementById('modalContent').classList.add('hidden');
            document.getElementById('modalError').classList.add('hidden');

            try {
                const response = await fetch(`/activity-logs/user/${currentUserId}?start_date=${startDate}&end_date=${endDate}`);
                const data = await response.json();

                if (data.success) {
                    renderSalesData(data);
                } else {
                    showModalError('Failed to load sales data.');
                }
            } catch (error) {
                console.error('Error fetching sales details:', error);
                showModalError('Network error. Please try again.');
            }
        }

        function renderSalesData(data) {
            document.getElementById('modalLoading').classList.add('hidden');
            document.getElementById('modalContent').classList.remove('hidden');

            // Update subtitle
            document.getElementById('modalSubtitle').textContent = `${data.user.role} at ${data.user.branch} — ${data.date}`;

            // Update summary cards
            document.getElementById('totalTransactions').textContent = data.total_sales;
            document.getElementById('grandTotal').textContent = `₱${data.grand_total}`;

            const container = document.getElementById('salesGroupedBody');
            const noSalesMsg = document.getElementById('noSalesMessage');

            if (!data.receipts || data.receipts.length === 0) {
                container.innerHTML = '';
                noSalesMsg.classList.remove('hidden');
                container.classList.add('hidden');
                return;
            }

            noSalesMsg.classList.add('hidden');
            container.classList.remove('hidden');

            container.innerHTML = data.receipts.map((receipt, index) => `
                <div class="mb-3 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-4 py-2.5 bg-gray-50 dark:bg-gray-700 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-simplicitea-100 text-simplicitea-800 dark:bg-simplicitea-900/40 dark:text-simplicitea-300">
                                Receipt #${receipt.receipt_number}
                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">${receipt.time}</span>
                        </div>
                        <span class="text-sm font-semibold text-gray-900 dark:text-black">₱${receipt.subtotal}</span>
                    </div>
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                        <thead>
                            <tr class="bg-white dark:bg-gray-800">
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Product</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Qty</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Unit Price</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                            ${receipt.items.map(item => `
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-4 py-2.5 text-sm font-medium text-gray-900 dark:text-black">${item.product_name}</td>
                                    <td class="px-4 py-2.5 text-sm text-gray-500 dark:text-gray-400 text-center">${item.quantity}</td>
                                    <td class="px-4 py-2.5 text-sm text-gray-500 dark:text-gray-400 text-right">₱${item.unit_price}</td>
                                    <td class="px-4 py-2.5 text-sm font-medium text-gray-900 dark:text-black text-right">₱${item.total_price}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `).join('');
        }

        function showModalError(message) {
            document.getElementById('modalLoading').classList.add('hidden');
            document.getElementById('modalContent').classList.add('hidden');
            document.getElementById('modalError').classList.remove('hidden');
            document.getElementById('modalErrorText').textContent = message;
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !document.getElementById('salesDetailsModal').classList.contains('hidden')) {
                closeDetailsModal();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const filtersForm = document.getElementById('cashierFiltersForm');
            if (!filtersForm) return;

            const liveFields = filtersForm.querySelectorAll('select, input[type="date"]');
            liveFields.forEach(field => {
                field.addEventListener('change', () => filtersForm.requestSubmit());
            });
        });

        // Live polling: auto-refresh activity logs every 30 seconds
        setInterval(() => {
            // Only reload if the modal is not open
            if (document.getElementById('salesDetailsModal').classList.contains('hidden')) {
                window.location.reload();
            }
        }, 30000);
    </script>
</x-app-layout>
