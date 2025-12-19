<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Daily Report - {{ $date }}</title>
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
    </style>
</head>

<body>
    <h1>Daily Transaction Report</h1>
    <p><strong>Date:</strong> {{ $date }}</p>

    <table>
        <thead>
            <tr>
                <th>Transaction Code</th>
                <th>Customer</th>
                <th>From Currency</th>
                <th>Amount From</th>
                <th>To Currency</th>
                <th>Amount To</th>
                <th>Rate</th>
                <th>Profit</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report['transactions'] ?? [] as $transaction)
                <tr>
                    <td>{{ $transaction->transaction_code }}</td>
                    <td>{{ $transaction->customer->name ?? 'N/A' }}</td>
                    <td>{{ $transaction->currencyFrom->code ?? 'N/A' }}</td>
                    <td>{{ number_format($transaction->amount_from, 2) }}</td>
                    <td>{{ $transaction->currencyTo->code ?? 'N/A' }}</td>
                    <td>{{ number_format($transaction->amount_to, 2) }}</td>
                    <td>{{ number_format($transaction->sell_rate, 4) }}</td>
                    <td>{{ number_format($transaction->profit ?? 0, 2) }}</td>
                    <td>{{ ucfirst($transaction->status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center;">No transactions found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <h3>Summary</h3>
        <p><strong>Total Transactions:</strong> {{ $report['total_transactions'] ?? 0 }}</p>
        <p><strong>Total Profit:</strong> {{ number_format($report['total_profit'] ?? 0, 2) }}</p>
    </div>

    <div class="footer">
        <p>Generated on {{ date('Y-m-d H:i:s') }}</p>
    </div>
</body>

</html>