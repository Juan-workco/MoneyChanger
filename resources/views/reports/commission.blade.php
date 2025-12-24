@extends('layouts.app')

@section('title', 'Commission Report - Money Changer Admin')

@section('content')
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom pb-3 pt-sm-3">
        <h1>Agent Commission Report</h1>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('reports.commission') }}" method="GET" class="form-inline">
                <div class="form-group mr-3">
                    <label for="month" class="mr-2">Month</label>
                    <input type="text" class="form-control" name="month" id="month-picker"
                        value="{{ request('month', now()->format('Y-m')) }}">
                </div>
                <div class="form-group mr-3">
                    <label for="agent_id" class="mr-2">Agent</label>
                    <select class="form-control" name="agent_id">
                        <option value="">All Agents</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ request('agent_id') == $agent->id ? 'selected' : '' }}>
                                {{ $agent->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Generate Report</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Agent Name</th>
                            <th>Total Transactions</th>
                            <th>Total Volume</th>
                            <th>Total Commission</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($commissions as $commission)
                            <tr>
                                <td>{{ $commission->agent_name }}</td>
                                <td>{{ $commission->transaction_count }}</td>
                                <td>{{ number_format($commission->total_volume, 2) }}</td>
                                <td class="font-weight-bold">{{ number_format($commission->total_commission, 2) }}</td>
                                <td>
                                    @if($commission->is_paid)
                                        <span class="badge badge-success">Paid</span>
                                    @else
                                        <span class="badge badge-warning">Unpaid</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No commission data found for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            flatpickr("#month-picker", {
                plugins: [
                    new monthSelectPlugin({
                        shorthand: true, //defaults to false
                        dateFormat: "Y-m", //defaults to "F Y"
                        altFormat: "F Y", //defaults to "F Y"
                        theme: "light" // defaults to "light"
                    })
                ]
            });
        });
    </script>
@endsection