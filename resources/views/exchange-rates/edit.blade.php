@extends('layouts.app')

@section('title', 'Edit Exchange Rate - Money Changer Admin')

@section('content')
    <div class="page-header">
        <h1>Edit Exchange Rate</h1>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('exchange-rates.update', $exchangeRate->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>From Currency</label>
                                <input type="text" class="form-control"
                                    value="{{ $exchangeRate->currencyFrom->code }} - {{ $exchangeRate->currencyFrom->name }}"
                                    disabled>
                            </div>

                            <div class="form-group col-md-6">
                                <label>To Currency</label>
                                <input type="text" class="form-control"
                                    value="{{ $exchangeRate->currencyTo->code }} - {{ $exchangeRate->currencyTo->name }}"
                                    disabled>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="buy_rate">Buy Rate <span class="text-danger">*</span></label>
                                <input type="number" step="0.0001"
                                    class="form-control {{ $errors->has('buy_rate') ? 'is-invalid' : '' }}" id="buy_rate"
                                    name="buy_rate" value="{{ old('buy_rate', $exchangeRate->buy_rate) }}" required>
                                @if ($errors->has('buy_rate'))
                                    <div class="invalid-feedback">{{ $errors->first('buy_rate') }}</div>
                                @endif
                            </div>

                            <div class="form-group col-md-6">
                                <label for="sell_rate">Sell Rate <span class="text-danger">*</span></label>
                                <input type="number" step="0.0001"
                                    class="form-control {{ $errors->has('sell_rate') ? 'is-invalid' : '' }}" id="sell_rate"
                                    name="sell_rate" value="{{ old('sell_rate', $exchangeRate->sell_rate) }}" required>
                                @if ($errors->has('sell_rate'))
                                    <div class="invalid-feedback">{{ $errors->first('sell_rate') }}</div>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="effective_date">Effective Date <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control {{ $errors->has('effective_date') ? 'is-invalid' : '' }}"
                                id="effective_date" name="effective_date"
                                value="{{ old('effective_date', $exchangeRate->effective_date->format('Y-m-d\TH:i')) }}"
                                required>
                            @if ($errors->has('effective_date'))
                                <div class="invalid-feedback">{{ $errors->first('effective_date') }}</div>
                            @endif
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active"
                                    value="1" {{ old('is_active', $exchangeRate->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Active</label>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Exchange Rate
                            </button>
                            <a href="{{ route('exchange-rates.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection