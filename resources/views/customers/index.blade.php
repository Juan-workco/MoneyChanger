@extends('layouts.app')

@section('title', 'Customers - Money Changer Admin')

@section('content')
    <div
        class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom pb-3 pt-sm-3">
        <h1>Customers</h1>
        @if(Auth::user()->hasPermission('manage_customers'))
            <a href="{{ route('customers.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Customer
            </a>
        @endif
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Agent</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                            <tr>
                                <td>
                                    <a href="{{ route('customers.show', $customer->id) }}">
                                        <strong>{{ $customer->name }}</strong>
                                    </a><br>
                                    <small class="text-muted">{{ $customer->country }}</small>
                                </td>
                                <td>
                                    <div><i class="fas fa-envelope text-muted mr-1"></i> {{ $customer->email }}</div>
                                    <div><i class="fas fa-phone text-muted mr-1"></i> {{ $customer->phone }}</div>
                                </td>
                                <td>{{ $customer->agent ? $customer->agent->name : '-' }}</td>
                                <td>
                                    @if($customer->is_active)
                                        <span class="badge badge-success badge-status">Active</span>
                                    @else
                                        <span class="badge badge-secondary badge-status">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    @if(Auth::user()->hasPermission('manage_customers'))
                                        <a href="{{ route('customers.edit', $customer->id) }}"
                                            class="btn btn-sm btn-info text-white" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                    <a href="{{ route('customers.transactions', $customer->id) }}"
                                        class="btn btn-sm btn-secondary" title="View Transactions">
                                        <i class="fas fa-history"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No customers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $customers->links() }}
            </div>
        </div>
    </div>
@endsection