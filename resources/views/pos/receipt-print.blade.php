<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $sale->receipt_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            line-height: 1.4;
            padding: 10px;
            max-width: 80mm;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }
        
        .store-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .branch-name {
            font-size: 12px;
            margin-bottom: 5px;
        }
        
        .receipt-info {
            font-size: 11px;
        }
        
        .items-table {
            width: 100%;
            margin: 10px 0;
        }
        
        .items-table th {
            text-align: left;
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding: 5px 0;
            font-size: 11px;
        }
        
        .items-table th:last-child,
        .items-table td:last-child {
            text-align: right;
        }
        
        .items-table td {
            padding: 5px 0;
            vertical-align: top;
        }
        
        .item-name {
            font-weight: bold;
        }
        
        .item-options {
            font-size: 10px;
            color: #666;
            margin-top: 2px;
        }
        
        .item-qty {
            text-align: center;
        }
        
        .totals {
            border-top: 1px dashed #000;
            margin-top: 10px;
            padding-top: 10px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
        }
        
        .total-row.grand-total {
            font-size: 14px;
            font-weight: bold;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 8px 0;
            margin: 5px 0;
        }
        
        .payment-info {
            margin-top: 10px;
            border-top: 1px dashed #000;
            padding-top: 10px;
        }
        
        .footer {
            text-align: center;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #000;
        }
        
        .footer-message {
            font-size: 11px;
            margin-bottom: 5px;
        }
        
        .cashier-info {
            font-size: 10px;
            color: #666;
            margin-top: 10px;
        }
        
        @media print {
            body {
                padding: 0;
            }
            
            .no-print {
                display: none !important;
            }
            
            @page {
                size: 80mm auto;
                margin: 5mm;
            }
        }
        
        .print-actions {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 8px;
        }
        
        .print-btn {
            padding: 12px 30px;
            font-size: 14px;
            cursor: pointer;
            background: #16a34a;
            color: white;
            border: none;
            border-radius: 6px;
            margin: 0 5px;
        }
        
        .print-btn:hover {
            background: #15803d;
        }
        
        .close-btn {
            padding: 12px 30px;
            font-size: 14px;
            cursor: pointer;
            background: #6b7280;
            color: white;
            border: none;
            border-radius: 6px;
            margin: 0 5px;
        }
        
        .close-btn:hover {
            background: #4b5563;
        }
    </style>
</head>
<body>
    <!-- Print Actions (hidden when printing) -->
    <div class="print-actions no-print">
        <button class="print-btn" onclick="window.print()">🖨️ Print Receipt</button>
        <button class="close-btn" onclick="window.close()">✕ Close</button>
    </div>

    <!-- Receipt Content -->
    <div class="header">
        <div class="store-name">{{ config('app.name') }}</div>
        <div class="branch-name">{{ $sale->branch->name ?? 'Main Branch' }}</div>
        <div class="receipt-info">
            <div>Receipt: {{ $sale->receipt_number }}</div>
            <div>{{ $sale->created_at->format('M d, Y h:i A') }}</div>
        </div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th>Item</th>
                <th style="text-align: center;">Qty</th>
                <th style="text-align: right;">Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->salesItems as $item)
            <tr>
                <td>
                    <div class="item-name">{{ $item->product->name ?? 'Product' }}</div>
                    @if(!empty($item->options) && is_array($item->options))
                        <div class="item-options">
                            @foreach($item->options as $optName => $optValue)
                                {{ $optName }}: {{ $optValue }}
                            @endforeach
                        </div>
                    @endif
                </td>
                <td class="item-qty">{{ $item->quantity }}</td>
                <td style="text-align: right;">₱{{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

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
            <span>Payment Method</span>
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
        <div class="footer-message">Please come again 🧋</div>
        <div class="cashier-info">
            Served by: {{ $sale->user->name ?? 'Staff' }}
        </div>
    </div>

    <script>
        // Auto-print when page loads (optional - comment out if not desired)
        window.onload = function() {
            // Small delay to ensure content is rendered
            setTimeout(function() {
                window.print();
            }, 500);
        };
        
        // Close window after printing (optional)
        window.onafterprint = function() {
            // Uncomment below to auto-close after printing
            // window.close();
        };
    </script>
</body>
</html>
