<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Receipt #{{ $sale->receipt_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        @page {
            size: 80mm auto;
            margin: 0;
        }
        
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            line-height: 1.3;
            width: 80mm;
            margin: 0 auto;
            padding: 5mm;
            background: white;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
        }
        
        .store-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 3px;
            letter-spacing: 1px;
        }
        
        .branch-name {
            font-size: 11px;
            margin-bottom: 3px;
        }
        
        .receipt-info {
            font-size: 10px;
        }
        
        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        
        .items {
            margin: 8px 0;
        }
        
        .item-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            font-size: 11px;
        }
        
        .item-name {
            flex: 1;
            font-weight: bold;
        }
        
        .item-options {
            font-size: 9px;
            color: #444;
            padding-left: 10px;
            margin-bottom: 2px;
        }
        
        .item-qty-price {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            padding-left: 10px;
        }
        
        .totals {
            border-top: 1px dashed #000;
            padding-top: 8px;
            margin-top: 8px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            margin-bottom: 3px;
        }
        
        .grand-total {
            font-size: 14px;
            font-weight: bold;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 5px 0;
            margin: 5px 0;
        }
        
        .payment-info {
            border-top: 1px dashed #000;
            padding-top: 8px;
            margin-top: 8px;
        }
        
        .footer {
            text-align: center;
            margin-top: 12px;
            padding-top: 8px;
            border-top: 1px dashed #000;
            font-size: 10px;
        }
        
        .footer-message {
            margin-bottom: 3px;
        }
        
        .cashier-info {
            font-size: 9px;
            color: #666;
            margin-top: 5px;
        }

        @media print {
            body {
                width: 80mm;
                padding: 2mm;
            }
            
            .no-print {
                display: none !important;
            }
        }

        /* Hide everything when not printing - for iframe approach */
        @media screen {
            body {
                visibility: visible;
            }
        }
    </style>
</head>
<body>
    <!-- Receipt Content -->
    <div class="header">
        <div class="store-name">ICY'S SIMPLICITEA</div>
        <div class="branch-name">{{ $sale->branch->name ?? 'Main Branch' }}</div>
        <div class="receipt-info">
            <div>Receipt: {{ $sale->receipt_number }}</div>
            <div>{{ $sale->created_at->format('M d, Y h:i A') }}</div>
        </div>
    </div>

    <div class="divider"></div>

    <div class="items">
        @foreach($sale->salesItems as $item)
        <div class="item-entry">
            <div class="item-row">
                <span class="item-name">{{ $item->product->name ?? 'Product' }}</span>
            </div>
            @if(!empty($item->options) && is_array($item->options))
            <div class="item-options">
                @foreach($item->options as $optName => $optValue)
                    {{ $optName }}: {{ $optValue }}@if(!$loop->last), @endif
                @endforeach
            </div>
            @endif
            <div class="item-qty-price">
                <span>{{ $item->quantity }} x ₱{{ number_format($item->unit_price, 2) }}</span>
                <span>₱{{ number_format($item->total_price, 2) }}</span>
            </div>
        </div>
        @endforeach
    </div>

    <div class="totals">
        <div class="total-row">
            <span>Subtotal</span>
            <span>₱{{ number_format($sale->subtotal, 2) }}</span>
        </div>
        @if($sale->tax_amount > 0)
        <div class="total-row">
            <span>Tax</span>
            <span>₱{{ number_format($sale->tax_amount, 2) }}</span>
        </div>
        @endif
        @if($sale->discount_amount > 0)
        <div class="total-row">
            <span>Discount</span>
            <span>-₱{{ number_format($sale->discount_amount, 2) }}</span>
        </div>
        @endif
        <div class="total-row grand-total">
            <span>TOTAL</span>
            <span>₱{{ number_format($sale->total_amount, 2) }}</span>
        </div>
    </div>

    <div class="payment-info">
        <div class="total-row">
            <span>Payment</span>
            <span>{{ ucfirst($sale->payment_method) }}</span>
        </div>
        <div class="total-row">
            <span>Amount Paid</span>
            <span>₱{{ number_format($sale->amount_paid, 2) }}</span>
        </div>
        <div class="total-row">
            <span>Change</span>
            <span>₱{{ number_format($sale->change_amount, 2) }}</span>
        </div>
    </div>

    <div class="footer">
        <div class="footer-message">Thank you for your purchase!</div>
        <div class="footer-message">Please come again</div>
        <div class="cashier-info">
            Served by: {{ $sale->user->name ?? 'Staff' }}
        </div>
    </div>

    <script>
        // Direct print function - triggers print immediately
        function directPrint() {
            // Focus the window to ensure print works
            window.focus();
            
            // Trigger print immediately - no delay for faster printing
            window.print();
            
            // Notify parent window that print was triggered
            if (window.parent && window.parent !== window) {
                window.parent.postMessage({ type: 'printTriggered', saleId: {{ $sale->id }} }, '*');
            }
        }

        // Auto-print when page loads - immediate execution
        if (document.readyState === 'complete') {
            directPrint();
        } else {
            window.onload = directPrint;
        }

        // Handle print completion
        window.onafterprint = function() {
            // Notify parent window
            if (window.parent && window.parent !== window) {
                window.parent.postMessage({ type: 'printComplete', saleId: {{ $sale->id }} }, '*');
            }
        };
    </script>
</body>
</html>
