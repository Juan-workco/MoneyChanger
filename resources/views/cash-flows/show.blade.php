@extends('layouts.app')

@section('title', 'Cash Flow Details - Money Changer Admin')

@section('content')
    <div
        class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom pb-3 pt-sm-3 mb-4">
        <h1>Cash Flow Details</h1>
        <div>
            <a href="{{ route('cash-flows.index') }}" class="btn btn-secondary mr-2">Back to List</a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        {{ $cashFlow->cash_flow_code }}
                        @if($cashFlow->status === 'pending')
                            <span class="badge badge-warning ml-2">Pending</span>
                        @elseif($cashFlow->status === 'completed')
                            <span class="badge badge-success ml-2">Completed</span>
                        @elseif($cashFlow->status === 'cancelled')
                            <span class="badge badge-danger ml-2">Cancelled</span>
                        @else
                            <span class="badge badge-secondary ml-2">{{ ucfirst($cashFlow->status) }}</span>
                        @endif
                        @if($cashFlow->is_backdated)
                            <span class="badge badge-danger ml-2" title="Backdated">Backdated</span>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Type:</strong>
                            <span class="d-block">
                                @if($cashFlow->type == 'ap')
                                    AP (Accounts Payable - Out)
                                @elseif($cashFlow->type == 'ar')
                                    AR (Accounts Receivable - In)
                                @elseif($cashFlow->type == 'ctc')
                                    CTC (Customer to Customer)
                                @endif
                            </span>
                        </div>
                        <div class="col-md-6 text-md-right">
                            <strong>Date:</strong>
                            <span class="d-block">{{ $cashFlow->transaction_date->format('Y-m-d H:i') }}</span>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <strong>Primary Customer:</strong>
                            <p class="h5">{{ $cashFlow->customer->name }}</p>
                        </div>
                        @if($cashFlow->type == 'ctc' && $cashFlow->relatedCustomer)
                            <div class="col-md-6">
                                <strong>To Customer (Receiver):</strong>
                                <p class="h5">{{ $cashFlow->relatedCustomer->name }}</p>
                            </div>
                        @endif
                        @if($cashFlow->type == 'ap' && $cashFlow->fromAccount)
                            <div class="col-md-6">
                                <strong>From Account:</strong>
                                <p class="h5">{{ $cashFlow->fromAccount->account_name }}
                                    ({{ $cashFlow->fromAccount->currency }})</p>
                            </div>
                        @endif
                        @if($cashFlow->type == 'ar' && $cashFlow->toAccount)
                            <div class="col-md-6">
                                <strong>To Account:</strong>
                                <p class="h5">{{ $cashFlow->toAccount->account_name }} ({{ $cashFlow->toAccount->currency }})
                                </p>
                            </div>
                        @endif
                    </div>

                    <div class="row mb-4 p-3 bg-light rounded">
                        <div class="col-md-6">
                            <strong>Amount:</strong>
                            <p class="h3 text-primary">{{ number_format($cashFlow->amount, 2) }}
                                {{ $cashFlow->currency->code }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <strong>Currency:</strong>
                            <p class="h5">{{ $cashFlow->currency->name }}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <strong>Notes:</strong>
                            <p class="text-muted">
                                @if($cashFlow->notes)
                                    {!! nl2br(e($cashFlow->notes)) !!}
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
                
                @if($cashFlow->status === 'pending' && Auth::user()->hasPermission('verify_cash_flows'))
                    <div class="card-body border-top bg-light">
                        <div class="d-flex justify-content-end">
                            <form action="{{ route('cash-flows.reject', $cashFlow->id) }}" method="POST" class="mr-2" onsubmit="return confirm('Are you sure you want to REJECT this cash flow?');">
                                @csrf
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </form>
                            <form action="{{ route('cash-flows.verify', $cashFlow->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to VERIFY this cash flow?');">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check"></i> Verify
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
                
                <div class="card-footer text-muted">
                    Created by {{ $cashFlow->creator->name }} on {{ $cashFlow->created_at->format('Y-m-d H:i') }}
                    
                    @if($cashFlow->status === 'completed' && $cashFlow->verifier)
                        <br>Verified by {{ $cashFlow->verifier->name }} on {{ $cashFlow->verified_at->format('Y-m-d H:i') }}
                    @elseif($cashFlow->status === 'cancelled' && $cashFlow->verifier)
                        <br>Rejected by {{ $cashFlow->verifier->name }} on {{ $cashFlow->verified_at->format('Y-m-d H:i') }}
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection