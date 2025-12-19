@extends('layouts.app')

@section('title', 'Edit Transaction - Money Changer Admin')

@section('content')
    <div class="page-header">
        <h1>Edit Transaction: {{ $transaction->transaction_code }}</h1>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('transactions.update', $transaction->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-8">
                                <h5 class="mb-3">Transaction Details</h5>

                                <div class="form-group">
                                    <label for="customer_id">Customer</label>
                                    <input type="text" class="form-control" value="{{ $transaction->customer->name }}"
                                        disabled>
                                    <input type="hidden" name="customer_id" value="{{ $transaction->customer_id }}">
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>From Currency</label>
                                        <input type="text" class="form-control"
                                            value="{{ $transaction->currencyFrom->code }}" disabled>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>To Currency</label>
                                        <input type="text" class="form-control" value="{{ $transaction->currencyTo->code }}"
                                            disabled>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label for="amount_from">Amount From</label>
                                        <input type="number" step="0.01" class="form-control" name="amount_from"
                                            value="{{ $transaction->amount_from }}" readonly>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="sell_rate">Exchange Rate</label>
                                        <input type="number" step="0.01" class="form-control" name="sell_rate"
                                            value="{{ $transaction->sell_rate }}" readonly>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="amount_to">Amount To</label>
                                        <input type="number" step="0.01" class="form-control" name="amount_to"
                                            value="{{ $transaction->amount_to }}" readonly>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="notes">Notes</label>
                                    <textarea class="form-control" id="notes" name="notes"
                                        rows="3">{{ old('notes', $transaction->notes) }}</textarea>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <h5 class="mb-3">Payment Details</h5>

                                <div class="form-group">
                                    <label for="transaction_date">Date</label>
                                    <input type="datetime-local" class="form-control" name="transaction_date"
                                        value="{{ $transaction->transaction_date->format('Y-m-d\TH:i') }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label for="payment_method">Payment Method</label>
                                    <select class="form-control {{ $errors->has('payment_method') ? 'is-invalid' : '' }}"
                                        id="payment_method" name="payment_method" required>
                                        <option value="cash" {{ old('payment_method', $transaction->payment_method) == 'cash' ? 'selected' : '' }}>Cash</option>
                                        <option value="bank_transfer" {{ old('payment_method', $transaction->payment_method) == 'bank_transfer' ? 'selected' : '' }}>Bank
                                            Transfer</option>
                                        <option value="cheque" {{ old('payment_method', $transaction->payment_method) == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 border-top pt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Transaction
                            </button>
                            <a href="{{ route('transactions.show', $transaction->id) }}"
                                class="btn btn-secondary ml-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection