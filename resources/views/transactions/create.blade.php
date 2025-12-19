@extends('layouts.app')

@section('title', 'New Transaction - Money Changer Admin')

@section('content')
    <div class="page-header">
        <h1>New Transaction</h1>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('transactions.store') }}" method="POST" id="transactionForm">
                        @csrf

                        <div class="row">
                            <div class="col-md-8">
                                <h5 class="mb-3">Transaction Details</h5>

                                <div class="form-group">
                                    <label for="customer_id">Customer <span class="text-danger">*</span></label>
                                    <select class="form-control @if ($errors->has('customer_id'))is-invalid @endif" id="customer_id"
                                        name="customer_id" required>
                                        <option value="">Select Customer</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }} ({{ $customer->phone }})</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('customer_id'))
                                        <div class="invalid-feedback d-block">{{ $errors->first('customer_id') }}</div>
                                    @endif
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="currency_from_display">From Currency (Base) <span class="text-danger">*</span></label>
                                        @php
                                            $defaultCurrencyObj = $currencies->firstWhere('code', $defaultCurrency);
                                        @endphp
                                        <input type="text" class="form-control" id="currency_from_display" 
                                            value="{{ $defaultCurrencyObj ? $defaultCurrencyObj->code . ' - ' . $defaultCurrencyObj->name : $defaultCurrency }}" 
                                            readonly style="background-color: #e9ecef;">
                                        <input type="hidden" name="currency_from_id" id="currency_from_id" value="{{ $defaultCurrencyObj ? $defaultCurrencyObj->id : '' }}">
                                        <small class="form-text text-muted">Base currency from settings</small>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="currency_to_id">To Currency <span class="text-danger">*</span></label>
                                        <select class="form-control @if ($errors->has('currency_to_id'))is-invalid @endif"
                                            id="currency_to_id" name="currency_to_id" required>
                                            <option value="">Select Currency</option>
                                            @foreach($currencies as $currency)
                                                <option value="{{ $currency->id }}" data-code="{{ $currency->code }}" {{ old('currency_to_id') == $currency->id ? 'selected' : '' }}>{{ $currency->code }} - {{ $currency->name }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('currency_to_id'))
                                            <div class="invalid-feedback d-block">{{ $errors->first('currency_to_id') }}</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label for="amount_from">Amount From <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01"
                                            class="form-control @if ($errors->has('amount_from'))is-invalid @endif" id="amount_from"
                                            name="amount_from" value="{{ old('amount_from') }}" required>
                                        @if ($errors->has('amount_from'))
                                            <div class="invalid-feedback d-block">{{ $errors->first('amount_from') }}</div>
                                        @endif
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="sell_rate">Exchange Rate</label>
                                        <input type="number" step="0.01" class="form-control" id="sell_rate"
                                            name="sell_rate" value="{{ old('sell_rate') }}" readonly>
                                        <small class="form-text text-muted" id="rate_display">Select currency pair</small>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="amount_to">Amount To</label>
                                        <input type="number" step="0.01" class="form-control" id="amount_to"
                                            name="amount_to" value="{{ old('amount_to') }}" readonly>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="notes">Notes</label>
                                    <textarea class="form-control" id="notes" name="notes"
                                        rows="3">{{ old('notes') }}</textarea>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <h5 class="mb-3">Payment Details</h5>

                                <div class="form-group">
                                    <label for="transaction_date">Date <span class="text-danger">*</span></label>
                                    <input type="date"
                                        class="form-control @if ($errors->has('transaction_date'))is-invalid @endif"
                                        id="transaction_date" name="transaction_date"
                                        value="{{ old('transaction_date', now()->format('Y-m-d')) }}" required>
                                    @if ($errors->has('transaction_date'))
                                        <div class="invalid-feedback d-block">{{ $errors->first('transaction_date') }}</div>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="payment_method">Payment Method <span class="text-danger">*</span></label>
                                    <select class="form-control @if ($errors->has('payment_method'))is-invalid @endif"
                                        id="payment_method" name="payment_method" required>
                                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                        <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                        <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                    </select>
                                    @if ($errors->has('payment_method'))
                                        <div class="invalid-feedback d-block">{{ $errors->first('payment_method') }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 border-top pt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Transaction
                            </button>
                            <a href="{{ route('transactions.index') }}" class="btn btn-secondary ml-2">Cancel</a>
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
            // Fetch exchange rate when To Currency changes
            $('#currency_to_id').change(function () {
                var fromId = $('#currency_from_id').val();
                var toId = $(this).val();
                var toCode = $(this).find(':selected').data('code');
                var fromCode = '{{ $defaultCurrency }}';

                if (fromId && toId) {
                    $.ajax({
                        url: '{{ route("exchange-rates.get-active") }}',
                        type: 'GET',
                        data: {
                            currency_from_id: fromId,
                            currency_to_id: toId
                        },
                        success: function (response) {
                            console.log(response)
                            if (response.success && response.rate) {
                                var rate = parseFloat(response.rate.sell_rate).toFixed(2);
                                $('#sell_rate').val(rate);
                                $('#rate_display').text(fromCode + ' to ' + toCode + ': ' + rate);
                                calculateAmountTo();
                            } else {
                                $('#sell_rate').val('');
                                $('#rate_display').html('<span class="text-danger">No rate found for this pair</span>');
                                $('#amount_to').val('');
                            }
                        },
                        error: function () {
                            $('#sell_rate').val('');
                            $('#rate_display').html('<span class="text-danger">Error fetching rate</span>');
                            $('#amount_to').val('');
                        }
                    });
                }
            });

            // Calculate Amount To when Amount From changes (using keyup for real-time)
            $('#amount_from').on('keyup input', function () {
                calculateAmountTo();
            });

            function calculateAmountTo() {
                var amountFrom = parseFloat($('#amount_from').val()) || 0;
                var rate = parseFloat($('#sell_rate').val()) || 0;
                var amountTo = amountFrom * rate;
                $('#amount_to').val(amountTo > 0 ? amountTo.toFixed(2) : '');
            }
        });
    </script>
@endsection