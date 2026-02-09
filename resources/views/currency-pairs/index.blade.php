@extends('layouts.app')

@section('title', 'Currency Pairs - Money Changer Admin')

@section('content')
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom pb-3 pt-sm-3">
        <h1>Currency Pairs</h1>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    Add New Currency Pair
                </div>
                <div class="card-body">
                    <form action="{{ route('currency-pairs.store') }}" method="POST" class="form-inline">
                        @csrf
                        
                        <label class="sr-only" for="base_currency_id">Base</label>
                        <select class="form-control mb-2 mr-sm-2" id="base_currency_id" name="base_currency_id" required>
                            <option value="">Base Currency</option>
                            @foreach($currencies as $currency)
                                <option value="{{ $currency->id }}" {{ old('base_currency_id') == $currency->id ? 'selected' : '' }}>
                                    {{ $currency->code }}
                                </option>
                            @endforeach
                        </select>

                        <label class="sr-only" for="target_currency_id">Target</label>
                        <div class="input-group mb-2 mr-sm-2">
                            <div class="input-group-prepend">
                                <div class="input-group-text"><i class="fas fa-arrow-right"></i></div>
                            </div>
                            <select class="form-control" id="target_currency_id" name="target_currency_id" required>
                                <option value="">Target Currency</option>
                                @foreach($currencies as $currency)
                                    <option value="{{ $currency->id }}" {{ old('target_currency_id') == $currency->id ? 'selected' : '' }}>
                                        {{ $currency->code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <label class="sr-only" for="default_point">Default Point</label>
                        <input type="number" step="0.0001" min="0" class="form-control mb-2 mr-sm-2" id="default_point" name="default_point" placeholder="Default Point (e.g. 0.05)" value="{{ old('default_point') }}" required>

                        <button type="submit" class="btn btn-primary mb-2">Add Pair</button>
                        
                        @if ($errors->any())
                            <div class="w-100 text-danger small mt-1">
                                {{ $errors->first() }}
                            </div>
                        @endif
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    Existing Pairs (Count: {{ $currencyPairs->count() }})
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Pair</th>
                                <th>Default Point</th>
                                <th class="text-center">Commission</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($currencyPairs as $pair)
                                <tr>
                                    <td>
                                        <strong>{{ $pair->baseCurrency->code }}</strong> 
                                        <span class="text-muted mx-2"><i class="fas fa-arrow-right"></i></span>
                                        <strong>{{ $pair->targetCurrency->code }}</strong>
                                    </td>
                                    <td>{{ number_format($pair->default_point, 4) }}</td>
                                    <td class="text-center">
                                        <form action="{{ route('currency-pairs.toggle-commission', $pair->id) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $pair->is_commission_enabled ? 'btn-success' : 'btn-secondary' }}" title="Click to Toggle">
                                                {{ $pair->is_commission_enabled ? 'Enabled' : 'Disabled' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-right">
                                        <form action="{{ route('currency-pairs.destroy', $pair->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this pair?');" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-3">No currency pairs defined yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
