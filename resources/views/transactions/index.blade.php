@extends('layouts.app')

@section('title', 'Transactions - Money Changer Admin')

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center">
        <h1>Transactions</h1>
        <a href="{{ route('transactions.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Transaction
        </a>
    </div>

    <!-- Bulk Actions Bar -->
    <div class="card mb-3" id="bulk-actions-bar" style="display: none;">
        <div class="card-body bg-light">
            <div class="d-flex align-items-center">
                <span class="mr-3">
                    <strong><span id="selected-count">0</span> selected</strong>
                </span>
                <div class="btn-group mr-2">
                    <button type="button" class="btn btn-success" onclick="bulkStatusChange('accept')">
                        <i class="fas fa-check"></i> Accept
                    </button>
                    <button type="button" class="btn btn-danger" onclick="bulkStatusChange('cancel')">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-info" onclick="bulkStatusChange('sent')">
                        <i class="fas fa-paper-plane"></i> Mark as Sent
                    </button>
                </div>
                <button type="button" class="btn btn-outline-secondary" onclick="clearSelection()">
                    Clear Selection
                </button>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('transactions.index') }}" method="GET" class="form-inline">
                <div class="form-group mr-2">
                    <label for="date_from" class="mr-2">From</label>
                    <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="form-group mr-2">
                    <label for="date_to" class="mr-2">To</label>
                    <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                </div>
                <div class="form-group mr-2">
                    <select class="form-control" name="status">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="accept" {{ request('status') == 'accept' ? 'selected' : '' }}>Accept</option>
                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="cancel" {{ request('status') == 'cancel' ? 'selected' : '' }}>Cancel</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary">Filter</button>
                <a href="{{ route('transactions.index') }}" class="btn btn-link">Reset</a>
            </form>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="30">
                                <input type="checkbox" id="select-all" onclick="toggleSelectAll()">
                            </th>
                            <th>Date</th>
                            <th>Code</th>
                            <th>Customer</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Rate</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                            <tr>
                                <td>
                                    <input type="checkbox" class="transaction-checkbox" value="{{ $transaction->id }}"
                                        data-status="{{ $transaction->status }}" onclick="updateBulkActions()">
                                </td>
                                <td>{{ $transaction->transaction_date->format('Y-m-d H:i') }}</td>
                                <td>
                                    <a href="{{ route('transactions.show', $transaction->id) }}">
                                        <strong>{{ $transaction->transaction_code }}</strong>
                                    </a>
                                </td>
                                <td>{{ $transaction->customer->name }}</td>
                                <td>{{ number_format($transaction->amount_from, 2) }} {{ $transaction->currencyFrom->code }}
                                </td>
                                <td>{{ number_format($transaction->amount_to, 2) }} {{ $transaction->currencyTo->code }}</td>
                                <td>{{ number_format($transaction->sell_rate, 2) }}</td>
                                <td>
                                    @if($transaction->status == 'accept')
                                        <span class="badge badge-success">Accepted</span>
                                    @elseif($transaction->status == 'pending')
                                        <span class="badge badge-warning">Pending</span>
                                    @elseif($transaction->status == 'sent')
                                        <span class="badge badge-info">Sent</span>
                                    @elseif($transaction->status == 'cancel')
                                        <span class="badge badge-danger">Cancelled</span>
                                    @else
                                        <span class="badge badge-secondary">{{ ucfirst($transaction->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('transactions.show', $transaction->id) }}"
                                            class="btn btn-sm btn-info text-white" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($transaction->status == 'pending')
                                            <a href="{{ route('transactions.edit', $transaction->id) }}"
                                                class="btn btn-sm btn-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No transactions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>

    <!-- Bulk Status Change Modal -->
    <div class="modal fade" id="bulkStatusModal" tabindex="-1" role="dialog" aria-labelledby="bulkStatusModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkStatusModalLabel">Confirm Status Change</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="bulkStatusModalBody">Are you sure you want to update the selected transactions?</p>
                    <div id="bulkStatusModalWarning" class="alert alert-warning" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmBulkStatusBtn">Confirm Update</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let pendingBulkActionIds = [];
        let pendingBulkActionStatus = '';

        function toggleSelectAll() {
            const selectAll = document.getElementById('select-all');
            const checkboxes = document.querySelectorAll('.transaction-checkbox');
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
            updateBulkActions();
        }

        function updateBulkActions() {
            const checked = document.querySelectorAll('.transaction-checkbox:checked');
            const count = checked.length;
            document.getElementById('selected-count').textContent = count;
            document.getElementById('bulk-actions-bar').style.display = count > 0 ? 'block' : 'none';
        }

        function clearSelection() {
            document.querySelectorAll('.transaction-checkbox').forEach(cb => cb.checked = false);
            document.getElementById('select-all').checked = false;
            updateBulkActions();
        }

        function bulkStatusChange(newStatus) {
            const checked = document.querySelectorAll('.transaction-checkbox:checked');
            const ids = [];
            let invalidTransactions = [];

            // Validate status workflow
            checked.forEach(cb => {
                const currentStatus = cb.dataset.status;
                const id = cb.value;

                // Workflow rules
                if (newStatus === 'accept' || newStatus === 'cancel') {
                    if (currentStatus !== 'pending') {
                        invalidTransactions.push(id);
                    } else {
                        ids.push(id);
                    }
                } else if (newStatus === 'sent') {
                    if (currentStatus !== 'accept') {
                        invalidTransactions.push(id);
                    } else {
                        ids.push(id);
                    }
                }
            });

            let warningMessage = '';
            if (invalidTransactions.length > 0) {
                if (newStatus === 'accept' || newStatus === 'cancel') {
                    warningMessage = 'Only PENDING transactions can be accepted or cancelled.<br>';
                } else if (newStatus === 'sent') {
                    warningMessage = 'Only ACCEPTED transactions can be marked as sent.<br>';
                }
                warningMessage += `<strong>${invalidTransactions.length} transaction(s) have invalid status and will be skipped.</strong>`;
            }

            if (ids.length === 0) {
                $('#bulkStatusModalLabel').text('Invalid Selection');
                $('#bulkStatusModalBody').html('No transactions selected or all selected transactions have invalid status for this action.');
                if (warningMessage) {
                    $('#bulkStatusModalWarning').html(warningMessage).show();
                } else {
                    $('#bulkStatusModalWarning').hide();
                }
                $('#confirmBulkStatusBtn').hide();
                $('#bulkStatusModal').modal('show');
                return;
            }

            // Prepare for confirmation
            pendingBulkActionIds = ids;
            pendingBulkActionStatus = newStatus;

            const statusLabel = newStatus === 'accept' ? 'Accepted' :
                newStatus === 'cancel' ? 'Cancelled' : 'Sent';

            $('#bulkStatusModalLabel').text('Confirm Status Change');
            $('#bulkStatusModalBody').html(`Change status to <strong>"${statusLabel}"</strong> for <strong>${ids.length}</strong> transaction(s)?`);

            if (warningMessage) {
                $('#bulkStatusModalWarning').html(warningMessage).show();
            } else {
                $('#bulkStatusModalWarning').hide();
            }

            $('#confirmBulkStatusBtn').show();
            $('#bulkStatusModal').modal('show');
        }

        // Handle Confirm Button Click
        document.getElementById('confirmBulkStatusBtn').addEventListener('click', function () {
            // Submit form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("transactions.bulk-update-status") }}';

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'status';
            statusInput.value = pendingBulkActionStatus;
            form.appendChild(statusInput);

            pendingBulkActionIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'transaction_ids[]';
                input.value = id;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        });
    </script>
@endsection