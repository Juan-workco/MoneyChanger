@extends('layouts.app')

@section('title', 'Daily Report - Money Changer Admin')

@section('content')
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom pb-3 pt-sm-3">
        <h1>Daily Transaction Report</h1>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('reports.daily') }}" method="GET" class="form-inline">
                <div class="form-group mr-3">
                    <label for="date" class="mr-2">Date</label>
                    <input type="date" class="form-control" name="date"
                        value="{{ request('date', now()->format('Y-m-d')) }}">
                </div>
                <button type="submit" class="btn btn-primary">Generate Report</button>
                <a href="{{ route('reports.export-pdf', ['date' => request('date', now()->format('Y-m-d'))]) }}"
                    class="btn btn-danger ml-2">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </a>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Summary for {{ \Carbon\Carbon::parse(request('date', now()))->format('d M Y') }}</h5>

            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6>Total Transactions</h6>
                            <h3>{{ $summary['total_count'] ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6>Total Volume (Base)</h6>
                            <h3>{{ number_format($summary['total_volume'] ?? 0, 2) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6>Total Profit</h6>
                            <h3 class="text-success">{{ number_format($summary['total_profit'] ?? 0, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Time</th>
                            <th>Transaction ID</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Amount From</th>
                            <th>Rate</th>
                            <th>Amount To</th>
                            <th>Status</th>
                            <th>Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->transaction_date->format('H:i') }}</td>
                                <td>{{ $transaction->transaction_code }}</td>
                                <td>{{ $transaction->customer->name }}</td>
                                <td>
                                    @if($transaction->type == 'buy')
                                        <span class="badge badge-success">Buy</span>
                                    @else
                                        <span class="badge badge-info">Sell</span>
                                    @endif
                                </td>
                                <td>{{ number_format($transaction->amount_from, 2) }} {{ $transaction->currencyFrom->code }}
                                </td>
                                <td>{{ number_format($transaction->sell_rate, 2) }}</td>
                                <td>{{ number_format($transaction->amount_to, 2) }} {{ $transaction->currencyTo->code }}</td>
                                <td>{{ ucfirst($transaction->status) }}</td>
                                <td class="text-right">{{ number_format($transaction->profit_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No transactions found for this date.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="font-weight-bold">
                            <td colspan="8" class="text-right">Total Profit:</td>
                            <td class="text-right">{{ number_format($summary['total_profit'] ?? 0, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection