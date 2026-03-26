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
                                        <label for="currency_from_id">From Currency <span class="text-danger">*</span></label>
                                        <select class="form-control @if ($errors->has('currency_from_id'))is-invalid @endif"
                                            id="currency_from_id" name="currency_from_id" required>
                                            <option value="">Select From Currency</option>
                                            @foreach($currencies as $currency)
                                                <option value="{{ $currency->id }}" data-code="{{ $currency->code }}" {{ old('currency_from_id') == $currency->id ? 'selected' : '' }}>{{ $currency->code }} - {{ $currency->name }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('currency_from_id'))
                                            <div class="invalid-feedback d-block">{{ $errors->first('currency_from_id') }}</div>
                                        @endif
                                        <small class="form-text text-muted">Select From Currency first to filter To Currency</small>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="currency_to_id">To Currency <span class="text-danger">*</span></label>
                                        <select class="form-control @if ($errors->has('currency_to_id'))is-invalid @endif"
                                            id="currency_to_id" name="currency_to_id" required>
                                            <option value="">Select To Currency</option>
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
                                        <label for="sell_rate">Exchange Rate <span class="text-danger">*</span></label>
                                        <input type="number" step="0.000001" class="form-control" id="sell_rate"
                                            name="sell_rate" value="{{ old('sell_rate') }}" required>
                                        <small class="form-text text-muted" id="rate_display">Select currency pair</small>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="amount_to">Amount To</label>
                                        <input type="number" step="0.01" class="form-control" id="amount_to"
                                            name="amount_to" value="{{ old('amount_to') }}" readonly>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="service_fee">Service Fee</label>
                                    <input type="number" step="0.01" class="form-control" id="service_fee"
                                        name="service_fee" value="{{ old('service_fee', 0) }}">
                                </div>

                                <div class="form-row">
                                    <!-- <h5 class="mb-3">Commission Calculation</h5> -->
                                    
                                    <input type="hidden" name="currency_pair_id" id="currency_pair_id">
                                    <input type="hidden" name="upline1_point" id="upline1_point">
                                    <input type="hidden" name="upline2_point" id="upline2_point">

                                    <div class="form-group col-md-6">
                                        <label for="upline1_commission_amount">Upline 1 Commission</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" class="form-control" 
                                                id="upline1_commission_amount" name="upline1_commission_amount" 
                                                value="{{ old('upline1_commission_amount') }}">
                                            <div class="input-group-append">
                                                <span class="input-group-text" id="upline1_point_display">Pt: -</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="upline2_commission_amount">Upline 2 Commission</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" class="form-control" 
                                                id="upline2_commission_amount" name="upline2_commission_amount" 
                                                value="{{ old('upline2_commission_amount') }}">
                                            <div class="input-group-append">
                                                <span class="input-group-text" id="upline2_point_display">Pt: -</span>
                                            </div>
                                        </div>
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
                        </div>

                        <div class="card-body">
                        </div>

                        <div class="card-body">
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
            // Data passed from controller
            var currencyPairs = @json($currencyPairs);
            var exchangeRates = @json($exchangeRates);
            var customerCommissions = {}; 
            
            // Build customer commission map efficiently
            // Structure: { customer_id: { pair_id: { upline1: point, upline2: point } } }
            @foreach($customers as $cust)
                customerCommissions[{{ $cust->id }}] = {};
                @foreach($cust->commissions as $comm)
                    if (!customerCommissions[{{ $cust->id }}][{{ $comm->currency_pair_id }}]) {
                        customerCommissions[{{ $cust->id }}][{{ $comm->currency_pair_id }}] = {};
                    }
                    customerCommissions[{{ $cust->id }}][{{ $comm->currency_pair_id }}]['{{ $comm->upline_level }}'] = {{ $comm->point_value }};
                @endforeach
            @endforeach

            var currentPairId = null;
            var currentPoints = { upline1: 0, upline2: 0 };
            var autoCalcUpline1 = true;
            var autoCalcUpline2 = true;

            // When From Currency changes, fetch available To Currencies via AJAX
            $('#currency_from_id').change(function() {
                var fromCurrencyId = $(this).val();
                var toSelect = $('#currency_to_id');
                
                // Reset To Currency
                toSelect.empty().append('<option value="">Select To Currency</option>');
                $('#sell_rate').val('');
                $('#rate_display').text('Select currency pair');
                $('#amount_to').val('');

                if (fromCurrencyId) {
                    $.ajax({
                        url: "{{ route('transactions.available-to-currencies') }}",
                        method: 'GET',
                        data: { from_currency_id: fromCurrencyId },
                        success: function(response) {
                            if (response.currencies && response.currencies.length > 0) {
                                response.currencies.forEach(function(currency) {
                                    var oldVal = '{{ old("currency_to_id", "") }}';
                                    var selected = (oldVal == currency.id) ? 'selected' : '';
                                    toSelect.append(
                                        '<option value="' + currency.id + '" data-code="' + currency.code + '" ' + selected + '>' + 
                                        currency.code + ' - ' + currency.name + '</option>'
                                    );
                                });
                                // If only one option, auto-select it
                                if (response.currencies.length === 1) {
                                    toSelect.val(response.currencies[0].id);
                                    toSelect.trigger('change');
                                }
                            } else {
                                toSelect.append('<option value="" disabled>No matching currencies</option>');
                            }
                        },
                        error: function() {
                            toSelect.append('<option value="" disabled>Error loading currencies</option>');
                        }
                    });
                }
            });

            // Fetch exchange rate when To Currency changes
            $('#currency_to_id').change(function() {
                fetchExchangeRate();
            });

            function fetchExchangeRate() {
                const currencyFrom = $('#currency_from_id').val();
                const currencyTo = $('#currency_to_id').val();
                const fromCode = $('#currency_from_id').find(':selected').data('code') || '';
                const toCode = $('#currency_to_id').find(':selected').data('code') || '';

                detectCurrencyPair(currencyFrom, currencyTo);

                if (currencyFrom && currencyTo && currencyFrom !== currencyTo) {
                    $.ajax({
                        url: "{{ route('exchange-rates.get-active-rate') }}",
                        method: 'GET',
                        data: {
                            currency_from_id: currencyFrom,
                            currency_to_id: currencyTo
                        },
                        success: function (response) {
                            if (response.success && response.rate) {
                                var rate = parseFloat(response.rate.sell_rate).toFixed(2);
                                $('#sell_rate').val(rate);
                                $('#rate_display').text(fromCode + ' to ' + toCode + ': ' + rate);
                                calculateAmountTo();
                                calculateCommissions();
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
            }

            // Detect Currency Pair ID from selected currencies
            function detectCurrencyPair(baseId, targetId) {
                currentPairId = null;
                for (var i = 0; i < currencyPairs.length; i++) {
                    if (currencyPairs[i].base_currency_id == baseId && currencyPairs[i].target_currency_id == targetId) {
                        currentPairId = currencyPairs[i].id;
                        $('#currency_pair_id').val(currentPairId);
                        break;
                    }
                }
                updateEffectivePoints();
            }

            // Update Effective Points based on Customer + Pair
            function updateEffectivePoints() {
                var customerId = $('#customer_id').val();
                
                // Reset to 0 first
                currentPoints.upline1 = 0;
                currentPoints.upline2 = 0;

                if (currentPairId) {
                    var pair = currencyPairs.find(p => p.id == currentPairId);
                    
                    if (pair && pair.is_commission_enabled === false) {
                        currentPoints.upline1 = 0;
                        currentPoints.upline2 = 0;
                        
                        $('#upline1_commission_amount').prop('readonly', true).val('');
                        $('#upline2_commission_amount').prop('readonly', true).val('');
                        $('#upline1_point_display').text('Disabled');
                        $('#upline2_point_display').text('Disabled');
                        
                        $('#upline1_point').val(0);
                        $('#upline2_point').val(0);
                        
                        calculateCommissions();
                        return;
                    } else {
                        $('#upline1_commission_amount').prop('readonly', false);
                        $('#upline2_commission_amount').prop('readonly', false);
                    }

                    var sysDefault = pair ? parseFloat(pair.default_point) : 0;

                    currentPoints.upline1 = sysDefault;
                    currentPoints.upline2 = sysDefault;

                    if (customerId && customerCommissions[customerId] && customerCommissions[customerId][currentPairId]) {
                        var overrides = customerCommissions[customerId][currentPairId];
                        if (overrides.upline1 !== undefined && overrides.upline1 !== null) {
                            currentPoints.upline1 = parseFloat(overrides.upline1);
                        }
                        if (overrides.upline2 !== undefined && overrides.upline2 !== null) {
                            currentPoints.upline2 = parseFloat(overrides.upline2);
                        }
                    }
                }

                $('#upline1_point_display').text('Pt: ' + currentPoints.upline1);
                $('#upline2_point_display').text('Pt: ' + currentPoints.upline2);
                
                $('#upline1_point').val(currentPoints.upline1);
                $('#upline2_point').val(currentPoints.upline2);

                calculateCommissions();
            }

            // Customer Change Listener
            $('#customer_id').change(function() {
                updateEffectivePoints();
            });

            // Calculate Amount To when Amount From changes
            $('#amount_from, #sell_rate').on('keyup input', function () {
                calculateAmountTo();
                calculateCommissions();
            });
            
            // Manual Commission Override Listeners
            $('#upline1_commission_amount').on('input', function() {
                autoCalcUpline1 = false;
                updateRemarks();
            });
            $('#upline2_commission_amount').on('input', function() {
                autoCalcUpline2 = false;
                updateRemarks();
            });

            function calculateAmountTo() {
                var amountFrom = parseFloat($('#amount_from').val()) || 0;
                var rate = parseFloat($('#sell_rate').val()) || 0;
                var amountTo = amountFrom * rate;
                $('#amount_to').val(amountTo > 0 ? amountTo.toFixed(2) : '');
            }

            function calculateCommissions() {
                var amountFrom = parseFloat($('#amount_from').val()) || 0;
                var rate = parseFloat($('#sell_rate').val()) || 0;

                if (amountFrom > 0 && rate > 0) {
                    if (autoCalcUpline1) {
                        var pt1 = currentPoints.upline1;
                        if (rate + pt1 !== 0) {
                            var comm1 = (amountFrom / rate) - (amountFrom / (rate + pt1));
                            $('#upline1_commission_amount').val(comm1.toFixed(2));
                        }
                    }

                    if (autoCalcUpline2) {
                        var pt2 = currentPoints.upline2;
                        if (rate + pt2 !== 0) {
                            var comm2 = (amountFrom / rate) - (amountFrom / (rate + pt2));
                            $('#upline2_commission_amount').val(comm2.toFixed(2));
                        }
                    }
                    
                    updateRemarks();
                }
            }
            
            function updateRemarks() {
                var remarks = [];
                
                if (autoCalcUpline1) {
                    remarks.push("Upline 1: Auto-calculated (Pt: " + currentPoints.upline1 + ")");
                } else {
                    remarks.push("Upline 1: Manual Override");
                }
                
                if (autoCalcUpline2) {
                    remarks.push("Upline 2: Auto-calculated (Pt: " + currentPoints.upline2 + ")");
                } else {
                    remarks.push("Upline 2: Manual Override");
                }
                
                var currentNotes = $('#notes').val();
                var lines = currentNotes.split('\n');
                var keptLines = [];
                
                for (var i = 0; i < lines.length; i++) {
                    var line = lines[i].trim();
                    if (line !== '' && !line.startsWith('Upline 1:') && !line.startsWith('Upline 2:')) {
                        keptLines.push(lines[i]);
                    }
                }
                
                var newNotes = keptLines.concat(remarks).join('\n');
                $('#notes').val(newNotes);
            }
            
            // Trigger initial update
            updateRemarks();

            // If old values exist (e.g. validation failed), re-trigger From Currency change
            @if(old('currency_from_id'))
                $('#currency_from_id').trigger('change');
            @endif
        });
    </script>
@endsection