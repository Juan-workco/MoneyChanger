@extends('layouts.app')

@section('title', 'Profit & Loss - Money Changer Admin')

@section('content')
    <div class="page-header">
        <h1>Profit & Loss Statement</h1>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('reports.profit-loss') }}" method="GET" class="form-inline">
                <div class="form-group mr-3">
                    <label for="date_from" class="mr-2">From</label>
                    <input type="date" class="form-control" name="date_from"
                        value="{{ request('date_from', now()->startOfMonth()->format('Y-m-d')) }}">
                </div>
                <div class="form-group mr-3">
                    <label for="date_to" class="mr-2">To</label>
                    <input type="date" class="form-control" name="date_to"
                        value="{{ request('date_to', now()->format('Y-m-d')) }}">
                </div>
                <button type="submit" class="btn btn-primary">Generate Report</button>
                <a href="{{ route('reports.export-profit-loss-pdf', request()->all()) }}" class="btn btn-danger ml-2">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </a>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Total Profit</h5>
                </div>
                <div class="card-body text-center">
                    <h1 class="display-4 text-success">{{ number_format($totalProfit, 2) }}</h1>
                    <p class="text-muted">Base Currency (USD)</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Transaction Volume</h5>
                </div>
                <div class="card-body text-center">
                    <h1 class="display-4 text-info">{{ $totalTransactions }}</h1>
                    <p class="text-muted">Total Transactions</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            Profit by Currency Pair
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Currency Pair</th>
                            <th>Transactions</th>
                            <th>Volume</th>
                            <th>Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($profitByPair as $pair)
                            <tr>
                                <td>{{ $pair['name'] }}</td>
                                <td>{{ $pair['count'] }}</td>
                                <td>{{ number_format($pair['volume'], 2) }}</td>
                                <td class="font-weight-bold text-success">{{ number_format($pair['profit'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection