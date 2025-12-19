@extends('layouts.app')

@section('title', 'Edit Currency - Money Changer Admin')

@section('content')
    <div class="page-header">
        <h1>Edit Currency: {{ $currency->code }}</h1>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('currencies.update', $currency->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="code">Currency Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control {{ $errors->has('code') ? 'is-invalid' : '' }}" id="code"
                                name="code" value="{{ old('code', $currency->code) }}" required maxlength="3"
                                style="text-transform: uppercase;">
                            @if ($errors->has('code'))
                                <div class="invalid-feedback">{{ $errors->first('code') }}</div>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="name">Currency Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" id="name"
                                name="name" value="{{ old('name', $currency->name) }}" required>
                            @if ($errors->has('name'))
                                <div class="invalid-feedback">{{ $errors->first('name') }}</div>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="symbol">Symbol</label>
                            <input type="text" class="form-control {{ $errors->has('symbol') ? 'is-invalid' : '' }}"
                                id="symbol" name="symbol" value="{{ old('symbol', $currency->symbol) }}">
                            @if ($errors->has('symbol'))
                                <div class="invalid-feedback">{{ $errors->first('symbol') }}</div>
                            @endif
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active"
                                    value="1" {{ old('is_active', $currency->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Active</label>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Currency
                            </button>
                            <a href="{{ route('currencies.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection