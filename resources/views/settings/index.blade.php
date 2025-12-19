@extends('layouts.app')

@section('title', 'General Settings - Money Changer Admin')

@section('content')
    <div class="page-header">
        <h1>General Settings</h1>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="list-group">
                <a href="{{ route('settings.index') }}" class="list-group-item list-group-item-action active">
                    <i class="fas fa-cog mr-2"></i> General
                </a>
                <a href="{{ route('settings.accounts') }}" class="list-group-item list-group-item-action">
                    <i class="fas fa-university mr-2"></i> Receiving Accounts
                </a>
                <a href="{{ route('settings.payment-methods') }}" class="list-group-item list-group-item-action">
                    <i class="fas fa-credit-card mr-2"></i> Payment Methods
                </a>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    System Configuration
                </div>
                <div class="card-body">
                    <form action="{{ route('settings.update-general') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="company_name">Company Name</label>
                            <input type="text" class="form-control" id="company_name" name="company_name"
                                value="{{ \App\SystemSetting::get('company_name') }}">
                        </div>

                        <div class="form-group">
                            <label for="company_address">Company Address</label>
                            <textarea class="form-control" id="company_address" name="company_address"
                                rows="3">{{ \App\SystemSetting::get('company_address') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="company_phone">Company Phone</label>
                            <input type="text" class="form-control" id="company_phone" name="company_phone"
                                value="{{ \App\SystemSetting::get('company_phone') }}">
                        </div>

                        <div class="form-group">
                            <label for="company_email">Company Email</label>
                            <input type="email" class="form-control" id="company_email" name="company_email"
                                value="{{ \App\SystemSetting::get('company_email') }}">
                        </div>

                        <hr>

                        <div class="form-group">
                            <label for="default_currency">Default Base Currency <span class="text-danger">*</span></label>
                            <select class="form-control {{ $errors->has('default_currency') ? 'is-invalid' : '' }}"
                                id="default_currency" name="default_currency" required>
                                <option value="">Select Currency</option>
                                @foreach($currencies as $currency)
                                    <option value="{{ $currency->code }}" {{ \App\SystemSetting::get('default_currency', 'USD') == $currency->code ? 'selected' : '' }}>
                                        {{ $currency->code }} - {{ $currency->name }}
                                    </option>
                                @endforeach
                            </select>
                            @if ($errors->has('default_currency'))
                                <div class="invalid-feedback">{{ $errors->first('default_currency') }}</div>
                            @endif
                            <small class="form-text text-muted">The currency used for reporting and base
                                calculations.</small>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection