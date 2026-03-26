@extends('layouts.app')

@section('title', 'Customer Statement - Money Changer Admin')

@section('content')
    <div class="page-header">
        <h1>Customer Statement</h1>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.customer-statement') }}" class="form-inline flex-wrap">
                <div class="form-group mr-3 mb-2">
                    <label for="customer_id" class="mr-2">Customer</label>
                    <select name="customer_id" id="customer_id" class="form-control" required>
                        <option value="">-- Select Customer --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ $customerId == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mr-3 mb-2">
                    <label for="start_date" class="mr-2">From</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="form-group mr-3 mb-2">
                    <label for="end_date" class="mr-2">To</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <button type="submit" class="btn btn-primary mb-2">
                    <i class="fas fa-search"></i> Generate
                </button>
            </form>
        </div>
    </div>

    @if($statement)
        <div class="card mb-3">
            <div class="card-header">
                <strong>{{ $statement['customer']->name }}</strong>
                <span class="text-muted ml-2">{{ $startDate }} — {{ $endDate }}</span>
            </div>
            <div class="card-body p-0">
                {{-- Current Balances Summary --}}
                @if($statement['balances']->isNotEmpty())
                    <div class="p-3 bg-light border-bottom">
                        <strong>Current Balances:</strong>
                        @foreach($statement['balances'] as $bal)
                            <span class="badge badge-{{ $bal->balance >= 0 ? 'success' : 'danger' }} ml-2">
                                {{ $bal->currency->code ?? 'N/A' }}: {{ number_format($bal->balance, 4) }}
                                {{ $bal->balance >= 0 ? '(Owes Us)' : '(We Owe)' }}
                            </span>
                        @endforeach
                    </div>
                @endif

                {{-- Transaction Entries --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Reference</th>
                                <th>Description</th>
                                <th class="text-right">Debit</th>
                                <th class="text-right">Credit</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($statement['entries'] as $entry)
                                <tr>
                                    <td>{{ $entry['date'] }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ $entry['type'] }}</span>
                                    </td>
                                    <td>{{ $entry['reference'] }}</td>
                                    <td>{{ $entry['description'] }}</td>
                                    <td class="text-right text-danger font-weight-bold">
                                        @if($entry['debit'] > 0)
                                            {{ number_format($entry['debit'], 4) }}
                                            <small class="text-muted">{{ $entry['currency_from'] }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-right text-success font-weight-bold">
                                        @if($entry['credit'] > 0)
                                            {{ number_format($entry['credit'], 4) }}
                                            <small class="text-muted">{{ $entry['currency_to'] }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $entry['status'] === 'sent' || $entry['status'] === 'confirmed' ? 'success' : ($entry['status'] === 'cancel' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($entry['status']) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No transactions found for this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection
