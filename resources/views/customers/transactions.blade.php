@extends('layouts.app')

@section('title', 'Customer Transactions - Money Changer Admin')

@section('content')
    <div
        class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom pb-3 pt-sm-3">
        <h1>Transactions: {{ $customer->name }}</h1>
        <a href="{{ route('customers.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Customers
        </a>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card stats-card">
                <div class="card-body">
                    <h5 class="card-title text-muted">Total Transactions</h5>
                    <h2 class="mb-0">{{ $customer->total_transactions }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stats-card">
                <div class="card-body">
                    <h5 class="card-title text-muted">Total Volume</h5>
                    <h2 class="mb-0">{{ number_format($customer->total_volume, 2) }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Transaction ID</th>
                            <th>Type</th>
                            <th>Amount From</th>
                            <th>Rate</th>
                            <th>Amount To</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <a href="{{ route('transactions.show', $transaction->id) }}">
                                        {{ $transaction->transaction_code }}
                                    </a>
                                </td>
                                <td>
                                    @if($transaction->type == 'buy')
                                        <span class="badge badge-success">Buy</span>
                                    @else
                                        <span class="badge badge-danger">Sell</span>
                                    @endif
                                </td>
                                <td>{{ number_format($transaction->amount_from, 2) }} {{ $transaction->currencyFrom->code }}
                                </td>
                                <td>{{ number_format($transaction->rate, 4) }}</td>
                                <td>{{ number_format($transaction->amount_to, 2) }} {{ $transaction->currencyTo->code }}</td>
                                <td>
                                    @if($transaction->status == 'sent')
                                        <span class="badge badge-success">Sent</span>
                                    @elseif($transaction->status == 'pending')
                                        <span class="badge badge-warning">Pending</span>
                                    @else
                                        <span class="badge badge-secondary">{{ ucfirst($transaction->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No transactions found for this customer.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
@endsection