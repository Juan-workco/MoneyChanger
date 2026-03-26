@extends('layouts.app')

@section('title', 'Merge Customers - Money Changer Admin')

@section('content')
    <div class="page-header">
        <h1>Merge Customers</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Warning:</strong> Merging is irreversible. All transactions, balances, transfers, and ledger entries
                from the secondary customer will be moved to the primary customer. The secondary customer will be
                deactivated.
            </div>

            <form action="{{ route('customers.merge') }}" method="POST"
                onsubmit="return confirm('Are you sure you want to merge these customers? This cannot be undone.');">
                @csrf

                <div class="row">
                    <div class="col-md-5">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <i class="fas fa-star"></i> Primary Customer (Keep)
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="primary_id">Select Primary Customer <span
                                            class="text-danger">*</span></label>
                                    <select name="primary_id" id="primary_id" class="form-control" required>
                                        <option value="">-- Select Primary --</option>
                                        @foreach($customers as $c)
                                            <option value="{{ $c->id }}" {{ old('primary_id') == $c->id ? 'selected' : '' }}>
                                                {{ $c->name }} ({{ $c->customer_code ?? 'N/A' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-arrow-left fa-2x text-muted"></i>
                    </div>

                    <div class="col-md-5">
                        <div class="card border-danger">
                            <div class="card-header bg-danger text-white">
                                <i class="fas fa-times-circle"></i> Secondary Customer (Merge Into Primary)
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="secondary_id">Select Secondary Customer <span
                                            class="text-danger">*</span></label>
                                    <select name="secondary_id" id="secondary_id" class="form-control" required>
                                        <option value="">-- Select Secondary --</option>
                                        @foreach($customers as $c)
                                            <option value="{{ $c->id }}" {{ old('secondary_id') == $c->id ? 'selected' : '' }}>
                                                {{ $c->name }} ({{ $c->customer_code ?? 'N/A' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-compress-arrows-alt"></i> Merge Customers
                    </button>
                    <a href="{{ route('customers.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection