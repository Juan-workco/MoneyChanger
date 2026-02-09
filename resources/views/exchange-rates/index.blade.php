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

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Monthly Fixed Rates
                        ({{ \Carbon\Carbon::createFromFormat('Y-m', $currentMonth)->format('F Y') }})</h5>
                    <small class="text-muted">Set the accounting rate for profit calculation this month.</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Currency Pair</th>
                                    <th>Active Buy Rate</th>
                                    <th>Active Sell Rate</th>
                                    <th>Monthly Fixed Rate</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pairs as $pair)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="font-weight-bold mr-2">{{ $pair->baseCurrency->code }}</div>
                                                <i class="fas fa-arrow-right text-muted mx-2"></i>
                                                <div class="font-weight-bold ml-2">{{ $pair->targetCurrency->code }}</div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($pair->activeRate)
                                                <span
                                                    class="text-success font-weight-bold">{{ number_format($pair->activeRate->buy_rate, 4) }}</span>
                                                <br>
                                                <small
                                                    class="text-muted">{{ $pair->activeRate->effective_date->format('d M') }}</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($pair->activeRate)
                                                <span
                                                    class="text-primary font-weight-bold">{{ number_format($pair->activeRate->sell_rate, 4) }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(Auth::user()->hasPermission('manage_exchange_rates'))
                                                <form action="{{ route('exchange-rates.store-monthly-rate') }}" method="POST"
                                                    class="d-flex align-items-center">
                                                    @csrf
                                                    <input type="hidden" name="currency_pair_id" value="{{ $pair->id }}">
                                                    <input type="hidden" name="month" value="{{ $currentMonth }}">
                                                    <div class="input-group" style="max-width: 200px;">
                                                        <input type="number" step="0.01" name="rate"
                                                            class="form-control form-control-sm"
                                                            value="{{ $pair->currentMonthlyRate ? $pair->currentMonthlyRate->rate : '' }}"
                                                            placeholder="0.00" required>
                                                        <div class="input-group-append">
                                                            <button class="btn btn-sm btn-outline-primary" type="submit">
                                                                <i class="fas fa-save"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>
                                            @else
                                                {{ $pair->currentMonthlyRate ? number_format($pair->currentMonthlyRate->rate, 8) : '-' }}
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('exchange-rates.history', $pair->id) }}"
                                                class="btn btn-sm btn-info text-white">
                                                <i class="fas fa-history mr-1"></i> View History
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No currency pairs found. Please add pairs in
                                            Settings.</td>
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