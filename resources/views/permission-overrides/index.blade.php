@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Permission Overrides</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Review and manage restricted action requests</p>
            </div>
            @if($pendingCount > 0)
                <div class="mt-4 sm:mt-0 inline-flex items-center px-4 py-2 bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-300 rounded-lg">
                    <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    {{ $pendingCount }} Pending Request{{ $pendingCount > 1 ? 's' : '' }}
                </div>
            @endif
        </div>

        <!-- Filters -->
        <div class="mb-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <form method="GET" action="{{ route('permission-override.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                @if(auth()->user()->isAdmin() && $branches->count() > 0)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Branch</label>
                        <select name="branch_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-simplicitea-500 focus:border-simplicitea-500 dark:bg-gray-700 dark:text-white">
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-simplicitea-500 focus:border-simplicitea-500 dark:bg-gray-700 dark:text-white">
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Status</option>
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="denied" {{ $status === 'denied' ? 'selected' : '' }}>Denied</option>
                        <option value="expired" {{ $status === 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
                    <input type="date" name="start_date" value="{{ $startDate }}"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-simplicitea-500 focus:border-simplicitea-500 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date</label>
                    <input type="date" name="end_date" value="{{ $endDate }}"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-simplicitea-500 focus:border-simplicitea-500 dark:bg-gray-700 dark:text-white">
                </div>
                <div class="flex items-end">
                    <button type="submit"
                        class="w-full px-4 py-2 bg-simplicitea-600 hover:bg-simplicitea-700 text-white rounded-lg transition-colors">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Overrides Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Requested By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Reason</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Resolved By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($overrides as $override)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 {{ $override->status === 'pending' ? 'bg-yellow-50 dark:bg-yellow-900/10' : '' }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-simplicitea-500 rounded-full flex items-center justify-center">
                                            <span class="text-white text-sm font-medium">{{ substr($override->requestedBy->name, 0, 1) }}</span>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $override->requestedBy->name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ ucfirst($override->requestedBy->role) }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-400">
                                        {{ $override->action_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white max-w-xs truncate">
                                    {{ $override->reason ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    @if($override->discount_percent)
                                        {{ $override->discount_percent }}% off
                                    @elseif($override->new_amount && $override->original_amount)
                                        ₱{{ number_format($override->original_amount, 2) }} → ₱{{ number_format($override->new_amount, 2) }}
                                    @elseif($override->original_amount)
                                        ₱{{ number_format($override->original_amount, 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $override->status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-400' : '' }}
                                        {{ $override->status === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400' : '' }}
                                        {{ $override->status === 'denied' ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400' : '' }}
                                        {{ $override->status === 'expired' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : '' }}">
                                        {{ ucfirst($override->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $override->approvedBy ? $override->approvedBy->name : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $override->requested_at->format('M d, h:i A') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($override->status === 'pending')
                                        <div class="flex space-x-2">
                                            <button onclick="approveOverride({{ $override->id }})"
                                                class="text-green-600 hover:text-green-700 dark:text-green-400 font-medium">
                                                Approve
                                            </button>
                                            <button onclick="denyOverride({{ $override->id }})"
                                                class="text-red-600 hover:text-red-700 dark:text-red-400 font-medium">
                                                Deny
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    No permission override requests found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($overrides->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $overrides->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Deny Modal -->
<div id="denyModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50" onclick="closeDenyModal()"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Deny Request</h3>
            <form id="denyForm" onsubmit="submitDeny(event)">
                <input type="hidden" id="denyOverrideId">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reason for denial (optional)</label>
                    <textarea id="denyReason" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-simplicitea-500 focus:border-simplicitea-500 dark:bg-gray-700 dark:text-white"
                        placeholder="Enter reason..."></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeDenyModal()"
                        class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                        Deny Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    async function approveOverride(id) {
        if (!confirm('Are you sure you want to approve this request?')) return;

        try {
            const response = await fetch(`/permission-overrides/${id}/approve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const data = await response.json();
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to approve');
            }
        } catch (error) {
            alert('An error occurred');
        }
    }

    function denyOverride(id) {
        document.getElementById('denyOverrideId').value = id;
        document.getElementById('denyModal').classList.remove('hidden');
    }

    function closeDenyModal() {
        document.getElementById('denyModal').classList.add('hidden');
    }

    async function submitDeny(event) {
        event.preventDefault();
        
        const id = document.getElementById('denyOverrideId').value;
        const reason = document.getElementById('denyReason').value;

        try {
            const response = await fetch(`/permission-overrides/${id}/deny`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ reason })
            });

            const data = await response.json();
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to deny');
            }
        } catch (error) {
            alert('An error occurred');
        }
    }

    // Live polling: auto-refresh when pending count changes
    let lastPendingCount = {{ $pendingCount }};
    setInterval(async () => {
        try {
            const response = await fetch('/permission-overrides/pending', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            if (!response.ok) return;
            const data = await response.json();
            if (data.count !== undefined && data.count !== lastPendingCount) {
                window.location.reload();
            }
        } catch (e) {}
    }, 10000);
</script>
@endsection
