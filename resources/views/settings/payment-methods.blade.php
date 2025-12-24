@extends('layouts.app')

@section('title', 'Payment Methods - Money Changer Admin')

@section('content')
<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom pb-3 pt-sm-3">
    <h1>Payment Methods</h1>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="list-group">
            <a href="{{ route('settings.index') }}" class="list-group-item list-group-item-action">
                <i class="fas fa-cog mr-2"></i> General
            </a>
            <a href="{{ route('settings.accounts') }}" class="list-group-item list-group-item-action">
                <i class="fas fa-university mr-2"></i> Receiving Accounts
            </a>
            <a href="{{ route('settings.payment-methods') }}" class="list-group-item list-group-item-action active">
                <i class="fas fa-credit-card mr-2"></i> Payment Methods
            </a>
        </div>
    </div>
    
    <div class="col-md-9">
        <div class="card">
            <div class="card-header">
                Configure Payment Methods
            </div>
            <div class="card-body">
                <form action="{{ route('settings.update-payment-methods') }}" method="POST">
                    @csrf
                    
                    <p class="text-muted mb-4">Select the payment methods available for transactions.</p>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="method_cash" name="payment_methods[]" value="cash" 
                                {{ in_array('cash', $activeMethods ?? []) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="method_cash">
                                <strong>Cash</strong>
                                <p class="text-muted small mb-0">Physical cash transactions.</p>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="method_bank" name="payment_methods[]" value="bank_transfer"
                                {{ in_array('bank_transfer', $activeMethods ?? []) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="method_bank">
                                <strong>Bank Transfer</strong>
                                <p class="text-muted small mb-0">Direct bank transfers to receiving accounts.</p>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="method_cheque" name="payment_methods[]" value="cheque"
                                {{ in_array('cheque', $activeMethods ?? []) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="method_cheque">
                                <strong>Cheque</strong>
                                <p class="text-muted small mb-0">Cheque payments.</p>
                            </label>
                        </div>
                    </div>

                    @if(Auth::user()->hasPermission('manage_settings'))
                    <button type="submit" class="btn btn-primary mt-3">Save Changes</button>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
