<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Receipt') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full sm:max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-bold">{{ config('app.name') }}</h3>
                            <div class="text-sm text-gray-600">{{ $sale->branch->name ?? '' }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm">Receipt: <span class="font-medium">{{ $sale->receipt_number }}</span></div>
                            <div class="text-sm">Date: <span class="font-medium">{{ $sale->created_at->format('Y-m-d H:i') }}</span></div>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-gray-600">
                                    <th>Item</th>
                                    <th class="text-right">Qty</th>
                                    <th class="text-right">Unit</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach($sale->salesItems as $item)
                                <tr class="align-top">
                                    <td class="py-3">
                                        <div class="font-medium">{{ $item->product->name ?? 'Product' }}</div>
                                        @if(!empty($item->options) && is_array($item->options))
                                            <div class="text-xs text-gray-600 mt-1">
                                                @foreach($item->options as $optName => $optValue)
                                                    <div>{{ $optName }}: {{ $optValue }}</div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-3 text-right">{{ $item->quantity }}</td>
                                    <td class="py-3 text-right">₱{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="py-3 text-right">₱{{ number_format($item->total_price, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 border-t border-gray-200 pt-4">
                        <div class="flex justify-between text-sm text-gray-700">
                            <div>Subtotal</div>
                            <div>₱{{ number_format($sale->subtotal, 2) }}</div>
                        </div>
                        <div class="flex justify-between text-sm text-gray-700">
                            <div>Tax</div>
                            <div>₱{{ number_format($sale->tax_amount, 2) }}</div>
                        </div>
                        <div class="flex justify-between text-sm text-gray-700">
                            <div>Discount</div>
                            <div>-₱{{ number_format($sale->discount_amount, 2) }}</div>
                        </div>
                        <div class="flex justify-between text-lg font-bold text-simplicitea-700 mt-3">
                            <div>Total</div>
                            <div>₱{{ number_format($sale->total_amount, 2) }}</div>
                        </div>
                        <div class="flex justify-between text-sm text-gray-700 mt-2">
                            <div>Paid</div>
                            <div>₱{{ number_format($sale->amount_paid, 2) }}</div>
                        </div>
                        <div class="flex justify-between text-sm text-gray-700">
                            <div>Change</div>
                            <div>₱{{ number_format($sale->change_amount, 2) }}</div>
                        </div>
                    </div>

                    <div class="mt-6 text-center text-xs text-gray-500">
                        Thank you for your purchase!
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-8 border-t border-gray-200 pt-6 no-print">
                        <div class="flex flex-col sm:flex-row gap-3 justify-center">
                            <button onclick="window.open('{{ route('pos.receipt.print', $sale->id) }}', '_blank')" 
                                    class="inline-flex items-center justify-center px-6 py-3 bg-simplicitea-600 text-black font-medium rounded-lg hover:bg-simplicitea-700 focus:outline-none focus:ring-2 focus:ring-simplicitea-500 focus:ring-offset-2 transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 002 2z"/>
                                </svg>
                                Print Receipt Again
                            </button>
                            
                            <a href="{{ route('dashboard', ['from_pos' => 1, 'success' => 1]) }}" 
                               class="inline-flex items-center justify-center px-6 py-3 bg-white border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-simplicitea-500 focus:ring-offset-2 transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                Back to Dashboard
                            </a>
                        </div>
                        
                        <div class="mt-4 text-center">
                            <p class="text-sm text-gray-500">
                                Need help? Contact support or check your 
                                <a href="{{ route('dashboard') }}" class="text-simplicitea-600 hover:text-simplicitea-800">dashboard</a> 
                                for sales history.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Auto-print receipt on page load -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Small delay to ensure page is fully rendered before opening print window
            setTimeout(function() {
                window.open('{{ route('pos.receipt.print', $sale->id) }}', '_blank');
            }, 500);
        });
    </script>
</x-app-layout>
