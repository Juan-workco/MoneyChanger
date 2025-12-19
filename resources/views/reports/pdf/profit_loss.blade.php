<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Profit & Loss Report - {{ $startDate }} to {{ $endDate }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }

        .total-row {
            font-weight: bold;
            background-color: #e7e7e7;
        }
    </style>
</head>

<body>
    <h1>Profit & Loss Report</h1>
    <p><strong>Period:</strong> {{ $startDate }} to {{ $endDate }}</p>

    <div class="summary">
        <h3>Summary</h3>
        <p><strong>Total Revenue:</strong> {{ number_format($report['total_revenue'] ?? 0, 2) }}</p>
        <p><strong>Total Expenses:</strong> {{ number_format($report['total_expenses'] ?? 0, 2) }}</p>
        <p><strong>Net Profit:</strong> {{ number_format($report['net_profit'] ?? 0, 2) }}</p>
    </div>

    <h2>Details by Currency Pair</h2>
    <table>
        <thead>
            <tr>
                <th>Currency Pair</th>
                <th>Transactions</th>
                <th>Total Volume</th>
                <th>Total Profit</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($report['by_currency']) && count($report['by_currency']) > 0)
                @foreach($report['by_currency'] as $currency => $data)
                    <tr>
                        <td>{{ $currency }}</td>
                        <td>{{ $data['count'] ?? 0 }}</td>
                        <td>{{ number_format($data['volume'] ?? 0, 2) }}</td>
                        <td>{{ number_format($data['profit'] ?? 0, 2) }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4" style="text-align: center;">No data available</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <p>Generated on {{ date('Y-m-d H:i:s') }}</p>
    </div>
</body>

</html>