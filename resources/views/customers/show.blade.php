@extends('layouts.app')

@section('title', $customer->name . ' - Customer Details')

@section('content')
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom pb-3 pt-sm-3 mb-4">
        <h1>Customer: {{ $customer->name }}</h1>
        <div>
            @if(Auth::user()->hasPermission('manage_customers'))
                <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-info text-white">
                    <i class="fas fa-edit"></i> Edit Customer
                </a>
            @endif
            <a href="{{ route('customers.transactions', $customer->id) }}" class="btn btn-secondary">
                <i class="fas fa-history"></i> View All Transactions
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Customer Profile -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user"></i> Profile Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="35%">Email:</th>
                            <td>{{ $customer->email ?: '-' }}</td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td>{{ $customer->phone }}</td>
                        </tr>
                        <tr>
                            <th>Country:</th>
                            <td>{{ $customer->country ?: '-' }}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                @if($customer->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                    <hr>
                    <h6 class="font-weight-bold">Address</h6>
                    <p class="text-muted">{{ $customer->address ?: 'No address provided.' }}</p>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="col-md-8">
            <div class="row">
                <div class="col-sm-6 col-lg-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body pb-3">
                            <div class="text-value">{{ $stats['total_transactions'] }}</div>
                            <div>Total Transactions</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card text-white bg-success">
                        <div class="card-body pb-3">
                            <div class="text-value">{{ $stats['sent_transactions'] }}</div>
                            <div>Sent Transactions</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body pb-3">
                            <div class="text-value">{{ $stats['pending_transactions'] }}</div>
                            <div>Pending Items</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card text-white bg-info">
                        <div class="card-body pb-3">
                            <div class="text-value">{{ number_format($stats['total_volume'], 2) }}</div>
                            <div>Total Volume (Sent)</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="card">
                <div class="card-header">
                    <strong>Recent Transactions</strong> (Last 50)
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Profit</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customer->transactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->transaction_code }}</td>
                                        <td>{{ $transaction->transaction_date->format('Y-m-d') }}</td>
                                        <td>
                                            @php
                                                $statusClass = [
                                                    'pending' => 'badge-warning',
                                                    'accept' => 'badge-info',
                                                    'sent' => 'badge-success',
                                                    'cancel' => 'badge-danger'
                                                ][$transaction->status] ?? 'badge-secondary';
                                            @endphp
                                            <span class="badge {{ $statusClass }}">
                                                {{ ucfirst($transaction->status) }}
                                            </span>
                                        </td>
                                        <td>{{ number_format($transaction->amount_from, 2) }}
                                            {{ $transaction->currencyFrom->code ?? '' }}</td>
                                        <td>{{ number_format($transaction->amount_to, 2) }}
                                            {{ $transaction->currencyTo->code ?? '' }}</td>
                                        <td>{{ number_format($transaction->profit_amount, 2) }}</td>
                                        <td>
                                            <a href="{{ route('transactions.show', $transaction->id) }}"
                                                class="btn btn-sm btn-link py-0">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No transactions recorded for this customer yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection