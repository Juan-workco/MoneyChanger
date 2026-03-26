@extends('layouts.app')

@section('title', 'Cash Flows (AP/AR/CTC) - Money Changer Admin')

@section('content')
    <div
        class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom pb-3 pt-sm-3">
        <h1>Cash Flows (AP/AR/CTC)</h1>
        <a href="{{ route('cash-flows.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Entry
        </a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('cash-flows.index') }}" method="GET">
                <div class="form-row">
                    <div class="form-group col-md-2">
                        <label for="date_from">From Date</label>
                        <input type="date" class="form-control" name="date_from" id="date_from"
                            value="{{ request('date_from') }}">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="date_to">To Date</label>
                        <input type="date" class="form-control" name="date_to" id="date_to"
                            value="{{ request('date_to') }}">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="type">Type</label>
                        <select class="form-control" name="type" id="type">
                            <option value="">All Types</option>
                            <option value="ap" {{ request('type') == 'ap' ? 'selected' : '' }}>AP (Accounts Payable)</option>
                            <option value="ar" {{ request('type') == 'ar' ? 'selected' : '' }}>AR (Accounts Receivable)
                            </option>
                            <option value="ctc" {{ request('type') == 'ctc' ? 'selected' : '' }}>CTC (Customer to Customer)
                            </option>
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="status">Status</label>
                        <select class="form-control" name="status" id="status">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="search">Search Code</label>
                        <input type="text" class="form-control" name="search" id="search" placeholder="Search Code..."
                            value="{{ request('search') }}">
                    </div>
                    <div class="form-group col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-secondary mr-2">Filter</button>
                        <a href="{{ route('cash-flows.index') }}" class="btn btn-link">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Code</th>
                            <th>Status</th>
                            <th>Type</th>
                            <th>Customer (Primary)</th>
                            <th>Related (Secondary)</th>
                            <th>Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cashFlows as $cf)
                            <tr>
                                <td>
                                    {{ $cf->transaction_date->format('Y-m-d') }}
                                    @if($cf->is_backdated)
                                        <span class="badge badge-danger" title="Backdated">BD</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('cash-flows.show', $cf->id) }}">
                                        <strong>{{ $cf->cash_flow_code }}</strong>
                                    </a>
                                </td>
                                <td>
                                    @if($cf->status === 'pending')
                                        <span class="badge badge-warning">Pending</span>
                                    @elseif($cf->status === 'completed')
                                        <span class="badge badge-success">Completed</span>
                                    @elseif($cf->status === 'cancelled')
                                        <span class="badge badge-danger">Cancelled</span>
                                    @else
                                        <span class="badge badge-secondary">{{ ucfirst($cf->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($cf->type == 'ap')
                                        <span class="badge badge-danger">AP (Out)</span>
                                    @elseif($cf->type == 'ar')
                                        <span class="badge badge-success">AR (In)</span>
                                    @else
                                        <span class="badge badge-info">CTC (Transfer)</span>
                                    @endif
                                </td>
                                <td>{{ $cf->customer->name }}</td>
                                <td>
                                    @if($cf->relatedCustomer)
                                        {{ $cf->relatedCustomer->name }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    {{ number_format($cf->amount, 2) }} {{ $cf->currency->code }}
                                </td>
                                <td>
                                    <a href="{{ route('cash-flows.show', $cf->id) }}" class="btn btn-sm btn-info text-white">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No cash flows found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $cashFlows->links() }}
            </div>
        </div>
    </div>
@endsection