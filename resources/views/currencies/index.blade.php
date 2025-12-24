@extends('layouts.app')

@section('title', 'Currencies - Money Changer Admin')

@section('content')
    <div
        class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom pb-3 pt-sm-3">
        <h1>Currencies</h1>
        @if(Auth::user()->hasPermission('manage_currencies'))
            <a href="{{ route('currencies.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Currency
            </a>
        @endif
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Symbol</th>
                            <th>Status</th>
                            @if(Auth::user()->hasPermission('manage_currencies'))
                                <th>Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($currencies as $currency)
                            <tr>
                                <td><strong>{{ $currency->code }}</strong></td>
                                <td>{{ $currency->name }}</td>
                                <td>{{ $currency->symbol }}</td>
                                <td>
                                    @if($currency->is_active)
                                        <span class="badge badge-success badge-status">Active</span>
                                    @else
                                        <span class="badge badge-secondary badge-status">Inactive</span>
                                    @endif
                                </td>
                                @if(Auth::user()->hasPermission('manage_currencies'))
                                    <td>
                                        <a href="{{ route('currencies.edit', $currency->id) }}"
                                            class="btn btn-sm btn-info text-white" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($currency->is_active)
                                            <form action="{{ route('currencies.deactivate', $currency->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-warning" title="Deactivate"
                                                    onclick="return confirm('Are you sure you want to deactivate this currency?')">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('currencies.activate', $currency->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" title="Activate">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No currencies found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $currencies->links() }}
            </div>
        </div>
    </div>
@endsection