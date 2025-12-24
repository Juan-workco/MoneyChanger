@extends('layouts.app')

@section('title', 'Balance Sheet - Money Changer Admin')

@section('content')
    <div class="page-header">
        <h1>Balance Sheet</h1>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('reports.balance-sheet') }}" method="GET" class="form-inline">
                <div class="form-group mr-3">
                    <label for="date" class="mr-2">Date</label>
                    <input type="date" class="form-control" name="date" id="date"
                        value="{{ request('date', now()->format('Y-m-d')) }}">
                </div>
                <button type="submit" class="btn btn-primary">Generate Report</button>
            </form>
        </div>
    </div>

    <div class="row">
        @foreach($balances as $currency => $amount)
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title text-muted mb-0">{{ $currency }}</h5>
                                <h2 class="mt-2 mb-0">{{ number_format($amount, 2) }}</h2>
                            </div>
                            <div class="icon-circle bg-primary text-white p-3 rounded-circle">
                                <i class="fas fa-coins fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card mt-4">
        <div class="card-header">
            Detailed Breakdown
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Currency</th>
                            <th>Opening Balance</th>
                            <th>In (Buy)</th>
                            <th>Out (Sell)</th>
                            <th>Closing Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($detailedBalances as $balance)
                            <tr>
                                <td><strong>{{ $balance['currency'] }}</strong></td>
                                <td>{{ number_format($balance['opening'], 2) }}</td>
                                <td class="text-success">+{{ number_format($balance['in'], 2) }}</td>
                                <td class="text-danger">-{{ number_format($balance['out'], 2) }}</td>
                                <td><strong>{{ number_format($balance['closing'], 2) }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection