@extends('layouts.app')

@section('title', 'New Cash Flow - Money Changer Admin')

@section('content')
    <div class="page-header border-bottom pb-3 pt-sm-3 mb-4">
        <h1>New Cash Flow Entry</h1>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    Create New Entry (AP / AR / CTC)
                </div>
                <div class="card-body">
                    <form action="{{ route('cash-flows.store') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="type">Transaction Type</label>
                            <select class="form-control" id="type" name="type" required onchange="toggleFields()">
                                <option value="">Select Type</option>
                                <option value="ap" {{ old('type') == 'ap' ? 'selected' : '' }}>AP (Accounts Payable - Out)</option>
                                <option value="ar" {{ old('type') == 'ar' ? 'selected' : '' }}>AR (Accounts Receivable - In)</option>
                                <option value="ctc" {{ old('type') == 'ctc' ? 'selected' : '' }}>CTC (Customer to Customer)</option>
                            </select>
                            <small class="form-text text-muted" id="type-help">Select the type of money movement.</small>
                        </div>

                        <div class="form-group">
                            <label for="transaction_date">Date</label>
                            <input type="date" class="form-control" id="transaction_date" name="transaction_date" 
                                value="{{ old('transaction_date', date('Y-m-d')) }}" required>
                        </div>

                        <!-- From Account for AP (appears before customer for AP flow) -->
                        <div class="form-group" id="from-account-group" style="display: none;">
                            <label for="from_account_id">From Account (Paying From)</label>
                            <select class="form-control" id="from_account_id" name="from_account_id">
                                <option value="">Select Account</option>
                                @foreach($receivingAccounts as $account)
                                    <option value="{{ $account->id }}" {{ old('from_account_id') == $account->id ? 'selected' : '' }}>
                                        {{ $account->account_name }} ({{ $account->currency }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="customer_id" id="customer-label">Customer</label>
                            <select class="form-control select2" id="customer_id" name="customer_id" required>
                                <option value="">Select Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- To Account for AR (appears after customer for AR flow) -->
                        <div class="form-group" id="to-account-group" style="display: none;">
                            <label for="to_account_id">To Account (Receiving To)</label>
                            <select class="form-control" id="to_account_id" name="to_account_id">
                                <option value="">Select Account</option>
                                @foreach($receivingAccounts as $account)
                                    <option value="{{ $account->id }}" {{ old('to_account_id') == $account->id ? 'selected' : '' }}>
                                        {{ $account->account_name }} ({{ $account->currency }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Related Customer for CTC -->
                        <div class="form-group" id="related-customer-group" style="display: none;">
                            <label for="related_customer_id">To Customer (Receiver)</label>
                            <select class="form-control select2" id="related_customer_id" name="related_customer_id">
                                <option value="">Select Receiver</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('related_customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="currency_id">Currency</label>
                                <select class="form-control" id="currency_id" name="currency_id" required>
                                    @foreach($currencies as $currency)
                                        <option value="{{ $currency->id }}" {{ old('currency_id') == $currency->id ? 'selected' : '' }}>
                                            {{ $currency->code }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-8">
                                <label for="amount">Amount</label>
                                <input type="number" step="0.01" min="0.01" class="form-control" id="amount" name="amount" 
                                    value="{{ old('amount') }}" placeholder="0.00" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Create Entry</button>
                        <a href="{{ route('cash-flows.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function toggleFields() {
            const type = document.getElementById('type').value;
            const relatedGroup = document.getElementById('related-customer-group');
            const fromAccountGroup = document.getElementById('from-account-group');
            const toAccountGroup = document.getElementById('to-account-group');
            const customerLabel = document.getElementById('customer-label');
            const typeHelp = document.getElementById('type-help');

            // Reset all fields
            relatedGroup.style.display = 'none';
            fromAccountGroup.style.display = 'none';
            toAccountGroup.style.display = 'none';
            document.getElementById('related_customer_id').required = false;
            document.getElementById('from_account_id').required = false;
            document.getElementById('to_account_id').required = false;

            if (type === 'ctc') {
                relatedGroup.style.display = 'block';
                document.getElementById('related_customer_id').required = true;
                customerLabel.textContent = 'From Customer (Sender)';
                typeHelp.textContent = 'Transfer from one customer to another.';
            } else if (type === 'ap') {
                fromAccountGroup.style.display = 'block';
                document.getElementById('from_account_id').required = true;
                customerLabel.textContent = 'To Customer (Payee)';
                typeHelp.textContent = 'We pay the customer (Outgoing).';
            } else if (type === 'ar') {
                toAccountGroup.style.display = 'block';
                document.getElementById('to_account_id').required = true;
                customerLabel.textContent = 'From Customer (Payer)';
                typeHelp.textContent = 'Customer pays us (Incoming).';
            } else {
                customerLabel.textContent = 'Customer';
                typeHelp.textContent = 'Select the type of money movement.';
            }
        }

        // Initialize on load
        document.addEventListener('DOMContentLoaded', function() {
            toggleFields();
            
            // Re-apply select2 if implemented globally
            if (typeof $.fn.select2 !== 'undefined') {
                $('.select2').select2({
                    theme: 'bootstrap4',
                    width: '100%'
                });
            }
        });
    </script>
@endsection
