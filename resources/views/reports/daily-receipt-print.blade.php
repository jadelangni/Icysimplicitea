<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Sales Report - {{ $date->format('M d, Y') }}</title>
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
        
        .report-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 5px;
        }
        
        .branch-name {
            font-size: 11px;
            margin-bottom: 3px;
        }
        
        .report-info {
            font-size: 10px;
        }
        
        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        
        .divider-double {
            border-top: 2px double #000;
            margin: 8px 0;
        }
        
        .section {
            margin: 8px 0;
        }
        
        .section-title {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .stat-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            font-size: 11px;
        }
        
        .stat-label {
            color: #444;
        }
        
        .stat-value {
            font-weight: bold;
        }
        
        .grand-total {
            font-size: 14px;
            font-weight: bold;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 5px 0;
            margin: 8px 0;
        }
        
        .payment-method {
            padding: 4px 0;
            border-bottom: 1px dotted #ccc;
        }
        
        .payment-method:last-child {
            border-bottom: none;
        }
        
        .sales-list {
            max-height: 200px;
            overflow: hidden;
        }
        
        .sale-item {
            font-size: 9px;
            padding: 3px 0;
            border-bottom: 1px dotted #eee;
        }
        
        .sale-item:last-child {
            border-bottom: none;
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
            font-size: 10px;
            margin-top: 5px;
            font-weight: bold;
        }
        
        .signature-line {
            margin-top: 20px;
            border-top: 1px solid #000;
            width: 60%;
            margin-left: auto;
            margin-right: auto;
        }
        
        .signature-label {
            text-align: center;
            font-size: 9px;
            margin-top: 3px;
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
        <div class="report-title">DAILY SALES REPORT</div>
        <div class="branch-name">{{ $branch->name ?? 'Main Branch' }}</div>
        <div class="report-info">
            <div>Date: {{ $date->format('F d, Y') }}</div>
            <div>Printed: {{ now()->format('M d, Y h:i A') }}</div>
        </div>
    </div>

    <div class="divider"></div>

    <!-- Summary Statistics -->
    <div class="section">
        <div class="section-title">Sales Summary</div>
        <div class="stat-row grand-total">
            <span>TOTAL REVENUE</span>
            <span>&#8369;{{ number_format($totalRevenue, 2) }}</span>
        </div>
        <div class="stat-row">
            <span class="stat-label">Total Transactions</span>
            <span class="stat-value">{{ number_format($totalTransactions) }}</span>
        </div>
        <div class="stat-row">
            <span class="stat-label">Average per Transaction</span>
            <span class="stat-value">&#8369;{{ number_format($averageTransaction, 2) }}</span>
        </div>
    </div>

    <div class="divider"></div>

    <!-- Payment Methods -->
    <div class="section">
        <div class="section-title">Payment Methods</div>
        @forelse($paymentMethods as $method => $data)
        <div class="payment-method">
            <div class="stat-row">
                <span class="stat-label">{{ ucfirst($method ?? 'Cash') }}</span>
                <span class="stat-value">&#8369;{{ number_format($data['total'], 2) }}</span>
            </div>
            <div style="font-size: 9px; color: #666; padding-left: 10px;">
                {{ $data['count'] }} transaction(s)
            </div>
        </div>
        @empty
        <div style="text-align: center; font-size: 10px; color: #666;">
            No transactions
        </div>
        @endforelse
    </div>

    <div class="divider-double"></div>

    <!-- Duty Staff for Today -->
    @if(isset($todayDutyStaff) && $todayDutyStaff->count() > 0)
    <div class="section">
        <div class="section-title">On Duty Today</div>
        @foreach($todayDutyStaff as $session)
        <div class="stat-row" style="font-size: 10px;">
            <span class="stat-label">{{ $session->user->name ?? 'Unknown' }}</span>
            <span class="stat-value">{{ $session->is_cashier ? 'Cashier' : 'Crew' }}</span>
        </div>
        <div style="font-size: 9px; color: #666; padding-left: 10px; margin-bottom: 4px;">
            In: {{ $session->logged_in_at->timezone('Asia/Manila')->format('h:i A') }}
            @if($session->logged_out_at)
             — Out: {{ $session->logged_out_at->timezone('Asia/Manila')->format('h:i A') }}
            @else
             — Still active
            @endif
        </div>
        @endforeach
    </div>

    <div class="divider"></div>
    @endif

    <!-- Cashier Info -->
    <div class="footer">
        <div class="cashier-info">
            Cashier: {{ $cashier->name ?? 'Staff' }}
        </div>
        <div class="report-info" style="margin-top: 5px;">
                @php
                    $currentCashierId = auth()->id() ?? ($cashier->id ?? null);
                @endphp
                Cashier ID: #{{ $currentCashierId ? str_pad($currentCashierId, 7, '0', STR_PAD_LEFT) : 'N/A' }}
        </div>
        
        <div class="signature-line"></div>
        <div class="signature-label">Cashier Signature</div>
        
        <div class="footer-message" style="margin-top: 15px;">
            --------------------------------
        </div>
        <div class="footer-message">End of Daily Report</div>
    </div>

    <script>
        // Direct print function - triggers print immediately
        function directPrint() {
            window.focus();
            window.print();
            
            // Notify parent window that print was triggered
            if (window.parent && window.parent !== window) {
                window.parent.postMessage({ type: 'dailyReportPrintTriggered' }, '*');
            }
        }

        // Auto-print when page loads
        if (document.readyState === 'complete') {
            directPrint();
        } else {
            window.onload = directPrint;
        }

        // Handle print completion - redirect to logout or close
        window.onafterprint = function() {
            @if($redirectAfterPrint ?? false)
            // If this is logout flow, complete the logout
            window.location.href = '{{ route("logout.complete") }}';
            @else
            // Otherwise just close or go back
            if (window.opener) {
                window.close();
            }
            @endif
        };
    </script>
</body>
</html>
