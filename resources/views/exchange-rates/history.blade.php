@extends('layouts.app')

@section('title', 'Exchange Rate History - Money Changer Admin')

@section('content')
    <div
        class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom pb-3 pt-sm-3">
        <h1>History: {{ $pair->baseCurrency->code }} <i class="fas fa-arrow-right text-muted mx-2"></i>
            {{ $pair->targetCurrency->code }}</h1>
        <div class="d-flex align-items-center">
            <a href="{{ route('exchange-rates.index') }}" class="btn btn-secondary mr-2">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            @if(Auth::user()->hasPermission('manage_exchange_rates'))
                <a href="{{ route('exchange-rates.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Rate
                </a>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Buy Rate</th>
                            <th>Sell Rate</th>
                            <th>Status</th>
                            <th>Created By</th>
                            @if(Auth::user()->hasPermission('manage_exchange_rates'))
                                <th>Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rates as $rate)
                            <tr>
                                <td>{{ $rate->effective_date->format('Y-m-d H:i:s') }}</td>
                                <td>{{ number_format($rate->buy_rate, 4) }}</td>
                                <td>{{ number_format($rate->sell_rate, 4) }}</td>
                                <td>
                                    @if($rate->is_active)
                                        <span class="badge badge-success badge-status">Active</span>
                                    @else
                                        <span class="badge badge-secondary badge-status">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ optional($rate->creator)->name ?? 'System' }}</td>
                                @if(Auth::user()->hasPermission('manage_exchange_rates'))
                                    <td>
                                        <a href="{{ route('exchange-rates.edit', $rate->id) }}"
                                            class="btn btn-sm btn-info text-white" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('exchange-rates.destroy', $rate->id) }}" method="POST"
                                            class="d-inline" onsubmit="return confirm('Are you sure?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No historical rates found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $rates->links() }}
            </div>
        </div>
    </div>
@endsection