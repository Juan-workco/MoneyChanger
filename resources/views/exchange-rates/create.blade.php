@extends('layouts.app')

@section('title', 'Add Exchange Rate - Money Changer Admin')

@section('content')
    <div class="page-header">
        <h1>Add New Exchange Rate</h1>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('exchange-rates.store') }}" method="POST">
                        @csrf

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="currency_from_display">From Currency (Base) <span
                                        class="text-danger">*</span></label>
                                @php
                                    $defaultCurrencyObj = $currencies->firstWhere('code', $defaultCurrency);
                                @endphp
                                <input type="text" class="form-control" id="currency_from_display"
                                    value="{{ $defaultCurrencyObj ? $defaultCurrencyObj->code . ' - ' . $defaultCurrencyObj->name : $defaultCurrency }}"
                                    readonly style="background-color: #e9ecef;">
                                <input type="hidden" name="currency_from_id"
                                    value="{{ $defaultCurrencyObj ? $defaultCurrencyObj->id : '' }}">
                                <small class="form-text text-muted">Base currency is set in Settings</small>
                                @if ($errors->has('currency_from_id'))
                                    <div class="invalid-feedback d-block">{{ $errors->first('currency_from_id') }}</div>
                                @endif
                            </div>

                            <div class="form-group col-md-6">
                                <label for="currency_to_id">To Currency <span class="text-danger">*</span></label>
                                <select class="form-control {{ $errors->has('currency_to_id') ? 'is-invalid' : '' }}"
                                    id="currency_to_id" name="currency_to_id" required>
                                    <option value="">Select Currency</option>
                                    @foreach($currencies as $currency)
                                        <option value="{{ $currency->id }}" {{ old('currency_to_id') == $currency->id ? 'selected' : '' }}>{{ $currency->code }} - {{ $currency->name }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('currency_to_id'))
                                    <div class="invalid-feedback">{{ $errors->first('currency_to_id') }}</div>
                                @endif
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="base_rate">Base Rate (Index Rate)</label>
                                <input type="number" step="0.000001" class="form-control" id="base_rate"
                                    placeholder="Optional. Enter base rate to auto-calculate margins.">
                                <small class="form-text text-muted">Entering a base rate will automatically apply configured
                                    buy/sell margins.</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="buy_rate">Buy Rate <span class="text-danger">*</span></label>
                                <input type="number" step="0.01"
                                    class="form-control {{ $errors->has('buy_rate') ? 'is-invalid' : '' }}" id="buy_rate"
                                    name="buy_rate" value="{{ old('buy_rate') }}" required>
                                @if ($errors->has('buy_rate'))
                                    <div class="invalid-feedback">{{ $errors->first('buy_rate') }}</div>
                                @endif
                            </div>

                            <div class="form-group col-md-6">
                                <label for="sell_rate">Sell Rate <span class="text-danger">*</span></label>
                                <input type="number" step="0.01"
                                    class="form-control {{ $errors->has('sell_rate') ? 'is-invalid' : '' }}" id="sell_rate"
                                    name="sell_rate" value="{{ old('sell_rate') }}" required>
                                @if ($errors->has('sell_rate'))
                                    <div class="invalid-feedback">{{ $errors->first('sell_rate') }}</div>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="effective_date">Effective Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control {{ $errors->has('effective_date') ? 'is-invalid' : '' }}"
                                id="effective_date" name="effective_date"
                                value="{{ old('effective_date', now()->format('Y-m-d')) }}" required>
                            @if ($errors->has('effective_date'))
                                <div class="invalid-feedback">{{ $errors->first('effective_date') }}</div>
                            @endif
                            <small class="form-text text-muted">Date when this rate becomes effective</small>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active"
                                    value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Active</label>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Exchange Rate
                            </button>
                            <a href="{{ route('exchange-rates.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            let currentMargin = { buy_markup: 0, sell_markup: 0, auto_apply: 0 };

            function fetchMargin() {
                let fromId = $('input[name="currency_from_id"]').val();
                let toId = $('#currency_to_id').val();

                if (fromId && toId) {
                    $.get('{{ route("rate-margins.get-margin") }}', { from_id: fromId, to_id: toId }, function (data) {
                        currentMargin = data;
                        if (currentMargin.auto_apply && $('#base_rate').val()) {
                            calculateRates();
                        }
                    });
                }
            }

            function calculateRates() {
                let baseRate = parseFloat($('#base_rate').val());
                if (!isNaN(baseRate)) {
                    let buyRate = baseRate + parseFloat(currentMargin.buy_markup);
                    let sellRate = baseRate + parseFloat(currentMargin.sell_markup);
                    $('#buy_rate').val(buyRate.toFixed(6));
                    $('#sell_rate').val(sellRate.toFixed(6));
                }
            }

            $('#currency_to_id').change(fetchMargin);

            $('#base_rate').on('input', function () {
                calculateRates();
            });
        });
    </script>
@endsection