@extends('layouts.app')

@section('title', 'Transaction Details - Money Changer Admin')

@section('styles')
    <style>
        @media print {

            .app-header,
            .sidebar,
            .app-footer,
            .breadcrumb {
                display: none !important;
            }

            .main {
                margin-left: 0 !important;
                padding: 0 !important;
            }

            .container-fluid {
                padding: 0 !important;
            }

            .card {
                border: none !important;
                margin-bottom: 0 !important;
            }

            .card-header {
                background-color: transparent !important;
                font-weight: bold;
                font-size: 1.2rem;
                border-bottom: 2px solid #eee !important;
            }

            body {
                background-color: white !important;
            }
        }
    </style>
@endsection

@section('content')
    <div
        class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom pb-3 pt-sm-3">
        <h1 class="mb-3 mb-md-0 h3">Transaction: {{ $transaction->transaction_code }}</h1>
        <div class="d-print-none text-nowrap">
            <a href="{{ route('transactions.index') }}" class="btn btn-secondary mb-1">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            <a href="javascript:void(0)" class="btn btn-info ml-md-2 mb-1" onclick="window.print()">
                <i class="fas fa-print"></i> Print Receipt
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    Transaction Details
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Date:</strong>
                            <p>{{ $transaction->transaction_date->format('d M Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Status:</strong>
                            <div class="mt-2">
                                @if($transaction->status == 'accept')
                                    <span class="badge badge-success"
                                        style="font-size: 14px; padding: 8px 12px;">Accepted</span>
                                @elseif($transaction->status == 'pending')
                                    <span class="badge badge-warning" style="font-size: 14px; padding: 8px 12px;">Pending
                                        Review</span>
                                @elseif($transaction->status == 'sent')
                                    <span class="badge badge-info" style="font-size: 14px; padding: 8px 12px;">Sent</span>
                                @elseif($transaction->status == 'cancel')
                                    <span class="badge badge-danger"
                                        style="font-size: 14px; padding: 8px 12px;">Cancelled</span>
                                @else
                                    <span class="badge badge-secondary"
                                        style="font-size: 14px; padding: 8px 12px;">{{ ucfirst($transaction->status) }}</span>
                                @endif
                            </div>
                            <small class="text-muted mt-2 d-block">Change status from the Transactions list</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>From Amount:</strong>
                            <h4 class="text-primary">{{ number_format($transaction->amount_from, 2) }}
                                {{ $transaction->currencyFrom->code }}
                            </h4>
                        </div>
                        <div class="col-md-6">
                            <strong>To Amount:</strong>
                            <h4 class="text-success">{{ number_format($transaction->amount_to, 2) }}
                                {{ $transaction->currencyTo->code }}
                            </h4>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Exchange Rate:</strong>
                            <p>1 {{ $transaction->currencyFrom->code }} = {{ number_format($transaction->sell_rate, 2) }}
                                {{ $transaction->currencyTo->code }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <strong>Payment Method:</strong>
                            <p>{{ ucfirst(str_replace('_', ' ', $transaction->payment_method)) }}</p>
                        </div>
                    </div>

                    @if($transaction->notes)
                        <div class="row">
                            <div class="col-12">
                                <strong>Notes:</strong>
                                <p class="text-muted">{{ $transaction->notes }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    Customer Details
                </div>
                <div class="card-body">
                    <h5>{{ $transaction->customer->name }}</h5>
                    <p class="mb-1"><i class="fas fa-envelope mr-2"></i> {{ $transaction->customer->email }}</p>
                    <p class="mb-1"><i class="fas fa-phone mr-2"></i> {{ $transaction->customer->phone }}</p>
                    <div class="mt-3 d-print-none">
                        <a href="{{ route('customers.transactions', $transaction->customer_id) }}"
                            class="btn btn-sm btn-outline-primary btn-block">
                            View Customer History
                        </a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    Agent Details
                </div>
                <div class="card-body">
                    @if($transaction->creator)
                        <p class="mb-1"><strong>Name:</strong> {{ $transaction->creator->name }}</p>
                        <p class="mb-1"><strong>Email:</strong> {{ $transaction->creator->email }}</p>
                    @else
                        <p class="text-muted">Directly entered (Admin).</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection