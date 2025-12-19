@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="page-header">
        <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
        <p class="text-muted">Welcome back, {{ Auth::user()->name }}!</p>
    </div>

    <!-- Stats Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="card stats-card" style="border-left-color: #3498db;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Transactions</h6>
                            <h3 class="mb-0">{{ number_format($stats['total_transactions']) }}</h3>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-receipt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stats-card" style="border-left-color: #2ecc71;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Today's Transactions</h6>
                            <h3 class="mb-0">{{ number_format($stats['today_transactions']) }}</h3>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-calendar-day fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stats-card" style="border-left-color: #f39c12;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Pending Transactions</h6>
                            <h3 class="mb-0">{{ number_format($stats['pending_transactions']) }}</h3>
                        </div>
                        <div class="text-warning">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stats-card" style="border-left-color: #9b59b6;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Customers</h6>
                            <h3 class="mb-0">{{ number_format($stats['total_customers']) }}</h3>
                        </div>
                        <div class="text-purple">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profit Cards -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-chart-line text-success"></i> Today's Profit
                    </h5>
                    <h2 class="text-success mb-0">RM {{ number_format($todayProfit, 2) }}</h2>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-calendar-alt text-info"></i> This Month's Profit
                    </h5>
                    <h2 class="text-info mb-0">RM {{ number_format($monthProfit, 2) }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-history"></i> Recent Transactions</h5>
                        <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-primary">
                            View All <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Code</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Exchange</th>
                                    <th>Profit</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTransactions as $transaction)
                                    <tr>
                                        <td>
                                            <a href="{{ route('transactions.show', $transaction->id) }}">
                                                <strong>{{ $transaction->transaction_code }}</strong>
                                            </a>
                                        </td>
                                        <td>{{ $transaction->customer->name }}</td>
                                        <td>
                                            {{ $transaction->currencyFrom->symbol }}{{ number_format($transaction->amount_from, 2) }}
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary">
                                                {{ $transaction->currencyFrom->code }} → {{ $transaction->currencyTo->code }}
                                            </span>
                                        </td>
                                        <td class="text-success font-weight-bold">
                                            RM {{ number_format($transaction->profit_amount, 2) }}
                                        </td>
                                        <td>
                                            @if($transaction->status == 'pending')
                                                <span class="badge badge-warning">Pending</span>
                                            @elseif($transaction->status == 'received')
                                                <span class="badge badge-info">Received</span>
                                            @elseif($transaction->status == 'sent')
                                                <span class="badge badge-primary">Sent</span>
                                            @else
                                                <span class="badge badge-danger">Cancelled</span>
                                            @endif
                                        </td>
                                        <td>{{ $transaction->transaction_date->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No transactions found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center mb-3">
                            <a href="{{ route('transactions.create') }}" class="btn btn-primary btn-block text-white">
                                <i class="fas fa-plus-circle fa-2x mb-2"></i><br>
                                New Transaction
                            </a>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <a href="{{ route('customers.create') }}" class="btn btn-success btn-block text-white">
                                <i class="fas fa-user-plus fa-2x mb-2"></i><br>
                                Add Customer
                            </a>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <a href="{{ route('exchange-rates.create') }}" class="btn btn-info btn-block text-white">
                                <i class="fas fa-chart-line fa-2x mb-2"></i><br>
                                Add Exchange Rate
                            </a>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <a href="{{ route('reports.daily') }}" class="btn btn-warning btn-block text-white">
                                <i class="fas fa-file-alt fa-2x mb-2"></i><br>
                                View Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection