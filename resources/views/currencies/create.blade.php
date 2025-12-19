@extends('layouts.app')

@section('title', 'Add Currency - Money Changer Admin')

@section('content')
    <div class="page-header">
        <h1>Add New Currency</h1>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('currencies.store') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="code">Currency Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @if($errors->has('code')) is-invalid @endif" id="code"
                                name="code" value="{{ old('code') }}" placeholder="e.g. USD" required maxlength="3"
                                style="text-transform: uppercase;">
                            @if ($errors->has('code'))
                                @foreach($errors->get('code') as $error)
                                    <div class="invalid-feedback" style="display: block;">{{ $error }}</div>
                                @endforeach
                            @endif
                            <small class="form-text text-muted">3-letter ISO currency code.</small>
                        </div>

                        <div class="form-group">
                            <label for="name">Currency Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @if($errors->has('name')) is-invalid @endif"id="name"
                                name="name" value="{{ old('name') }}" placeholder="e.g. US Dollar" required>
                            @if ($errors->has('name'))
                                @foreach($errors->get('name') as $error)
                                    <div class="invalid-feedback" style="display: block;">{{ $error }}</div>
                                @endforeach
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="symbol">Symbol</label>
                            <input type="text" class="form-control" id="symbol"
                                name="symbol" value="{{ old('symbol') }}" placeholder="e.g. $">
                            @if ($errors->has('symbol'))
                                @foreach($errors->get('symbol') as $error)
                                    <div class="invalid-feedback" style="display: block;">{{ $error }}</div>
                                @endforeach
                            @endif
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active"
                                    value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Active</label>
                            </div>
                            <small class="form-text text-muted">Inactive currencies cannot be used in transactions.</small>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Currency
                            </button>
                            <a href="{{ route('currencies.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection