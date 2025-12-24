@extends('layouts.app')

@section('title', 'Exchange Rates - Money Changer Admin')

@section('content')
    <div
        class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom pb-3 pt-sm-3">
        <h1>Exchange Rates</h1>
        @if(Auth::user()->hasPermission('manage_exchange_rates'))
            <a href="{{ route('exchange-rates.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Rate
            </a>
        @endif
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Currency Pair</th>
                            <th>Buy Rate</th>
                            <th>Sell Rate</th>
                            <th>Effective Date</th>
                            <th>Status</th>
                            @if(Auth::user()->hasPermission('manage_exchange_rates'))
                                <th>Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rates as $rate)
                            <tr>
                                <td>
                                    <strong>{{ $rate->currencyFrom->code }}</strong> <i
                                        class="fas fa-arrow-right text-muted mx-1"></i>
                                    <strong>{{ $rate->currencyTo->code }}</strong>
                                </td>
                                <td>{{ number_format($rate->buy_rate, 4) }}</td>
                                <td>{{ number_format($rate->sell_rate, 4) }}</td>
                                <td>{{ $rate->effective_date->format('Y-m-d H:i') }}</td>
                                <td>
                                    @if($rate->is_active)
                                        <span class="badge badge-success badge-status">Active</span>
                                    @else
                                        <span class="badge badge-secondary badge-status">Inactive</span>
                                    @endif
                                </td>
                                @if(Auth::user()->hasPermission('manage_exchange_rates'))
                                    <td>
                                        <a href="{{ route('exchange-rates.edit', $rate->id) }}"
                                            class="btn btn-sm btn-info text-white" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No exchange rates found.</td>
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