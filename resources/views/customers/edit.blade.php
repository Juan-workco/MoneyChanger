@extends('layouts.app')

@section('title', 'Edit Customer - Money Changer Admin')

@section('content')
    <div class="page-header">
        <h1>Edit Customer: {{ $customer->name }}</h1>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('customers.update', $customer->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-3">Personal Information</h5>

                                <div class="form-group">
                                    <label for="name">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                        id="name" name="name" value="{{ old('name', $customer->name) }}" required>
                                    @if ($errors->has('name'))
                                        <div class="invalid-feedback">{{ $errors->first('name') }}</div>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                        id="email" name="email" value="{{ old('email', $customer->email) }}">
                                    @if ($errors->has('email'))
                                        <div class="invalid-feedback">{{ $errors->first('email') }}</div>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="phone">Phone Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control {{ $errors->has('phone') ? 'is-invalid' : '' }}"
                                        id="phone" name="phone" value="{{ old('phone', $customer->phone) }}" required>
                                    @if ($errors->has('phone'))
                                        <div class="invalid-feedback">{{ $errors->first('phone') }}</div>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <textarea class="form-control {{ $errors->has('address') ? 'is-invalid' : '' }}"
                                        id="address" name="address"
                                        rows="3">{{ old('address', $customer->address) }}</textarea>
                                    @if ($errors->has('address'))
                                        <div class="invalid-feedback">{{ $errors->first('address') }}</div>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="country">Country</label>
                                    <input type="text"
                                        class="form-control {{ $errors->has('country') ? 'is-invalid' : '' }}" id="country"
                                        name="country" value="{{ old('country', $customer->country) }}">
                                    @if ($errors->has('country'))
                                        <div class="invalid-feedback">{{ $errors->first('country') }}</div>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active"
                                            value="1" {{ old('is_active', $customer->is_active) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_active">Active Customer</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Customer
                            </button>
                            <a href="{{ route('customers.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection