<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Low Stock Alert</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f3f4f6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .card { background: #000000; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #16a34a, #15803d); padding: 24px 30px; text-align: center; }
        .header h1 { color: #000000; margin: 0; font-size: 22px; font-weight: 700; }
        .header p { color: #bbf7d0; margin: 8px 0 0; font-size: 14px; }
        .alert-banner { background: #fef2f2; border-left: 4px solid #ef4444; padding: 16px 24px; margin: 20px 24px 0; border-radius: 8px; }
        .alert-banner p { margin: 0; color: #991b1b; font-weight: 600; font-size: 15px; }
        .alert-banner span { font-weight: 400; color: #b91c1c; font-size: 13px; }
        .content { padding: 24px 30px; }
        .summary { display: flex; gap: 12px; margin-bottom: 20px; }
        .summary-box { flex: 1; background: #f9fafb; border-radius: 8px; padding: 12px; text-align: center; border: 1px solid #e5e7eb; }
        .summary-box .number { font-size: 28px; font-weight: 700; color: #ef4444; }
        .summary-box .label { font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th { background: #f9fafb; padding: 10px 14px; text-align: left; font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb; }
        td { padding: 12px 14px; border-bottom: 1px solid #f3f4f6; font-size: 14px; color: #374151; }
        .critical { background: #fef2f2; }
        .low { background: #000beb; }
        .badge-critical { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; background: #fee2e2; color: #dc2626; }
        .badge-low { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; background: #fef3c7; color: #d97706; }
        .footer { padding: 20px 30px; text-align: center; border-top: 1px solid #e5e7eb; }
        .footer p { margin: 0; color: #9ca3af; font-size: 12px; }
        .btn { display: inline-block; padding: 12px 28px; background: linear-gradient(135deg, #16a34a, #15803d); color: #000000; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 14px; margin-top: 16px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>🧋 Icy's Simplicitea POS</h1>
                <p>Low Stock Alert Notification</p>
            </div>

            <div class="alert-banner">
                <p>⚠️ {{ $totalCount }} item(s) are running low on stock</p>
                <span>Immediate attention may be required</span>
            </div>

            <div class="content">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Branch</th>
                            <th>Stock</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($alerts as $alert)
                        <tr class="{{ $alert['quantity'] <= 0 ? 'critical' : 'low' }}">
                            <td><strong>{{ $alert['product_name'] }}</strong></td>
                            <td>{{ $alert['branch_name'] }}</td>
                            <td>{{ $alert['quantity'] }} units</td>
                            <td>
                                @if($alert['quantity'] <= 0)
                                    <span class="badge-critical">Out of Stock</span>
                                @else
                                    <span class="badge-low">Low Stock</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div style="text-align: center; margin-top: 24px;">
                    <a href="{{ url('/product-inventory') }}" class="btn">View Inventory</a>
                </div>
            </div>

            <div class="footer">
                <p>This is an automated alert from Icy's Simplicitea POS System</p>
                <p>{{ now()->format('F d, Y h:i A') }}</p>
            </div>
        </div>
    </div>
</body>
</html>
