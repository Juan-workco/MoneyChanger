@extends('layouts.app')

@section('title', 'Receiving Accounts - Money Changer Admin')

@section('content')
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom pb-3 pt-sm-3">
        <h1>Receiving Accounts</h1>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="list-group">
                <a href="{{ route('settings.index') }}" class="list-group-item list-group-item-action">
                    <i class="fas fa-cog mr-2"></i> General
                </a>
                <a href="{{ route('settings.accounts') }}" class="list-group-item list-group-item-action active">
                    <i class="fas fa-university mr-2"></i> Receiving Accounts
                </a>
                <a href="{{ route('settings.payment-methods') }}" class="list-group-item list-group-item-action">
                    <i class="fas fa-credit-card mr-2"></i> Payment Methods
                </a>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Manage Accounts</span>
                    @if(Auth::user()->hasPermission('manage_settings'))
                        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addAccountModal">
                            <i class="fas fa-plus"></i> Add Account
                        </button>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Bank Name</th>
                                    <th>Account Name</th>
                                    <th>Account Number</th>
                                    <th>Currency</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($accounts as $account)
                                    <tr>
                                        <td>{{ $account->bank_name }}</td>
                                        <td>{{ $account->account_name }}</td>
                                        <td>{{ $account->account_number }}</td>
                                        <td>{{ $account->currency }}</td>
                                        <td>
                                            @if($account->is_active)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        @if(Auth::user()->hasPermission('manage_settings'))
                                        <td>
                                            <button class="btn btn-sm btn-info text-white edit-account-btn"
                                                data-id="{{ $account->id }}" data-type="{{ $account->account_type }}"
                                                data-bank="{{ $account->bank_name }}" data-name="{{ $account->account_name }}"
                                                data-number="{{ $account->account_number }}"
                                                data-currency="{{ $account->currency }}" data-active="{{ $account->is_active }}"
                                                data-toggle="modal" data-target="#editAccountModal">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('settings.delete-account', $account->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Are you sure you want to delete this account?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No receiving accounts found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Account Modal -->
    <div class="modal fade" id="addAccountModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Receiving Account</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form action="{{ route('settings.store-account') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Account Type <span class="text-danger">*</span></label>
                            <select class="form-control" name="account_type" required>
                                <option value="">Select Type</option>
                                <option value="bank">Bank Account</option>
                                <option value="usdt">USDT Wallet</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Bank Name</label>
                            <input type="text" class="form-control" name="bank_name">
                        </div>
                        <div class="form-group">
                            <label>Account Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="account_name" required>
                        </div>
                        <div class="form-group">
                            <label>Account Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="account_number" required>
                        </div>
                        <div class="form-group">
                            <label>Currency <span class="text-danger">*</span></label>
                            <select class="form-control" name="currency" required>
                                <option value="">Select Currency</option>
                                @foreach($currencies as $currency)
                                    <option value="{{ $currency->code }}">{{ $currency->code }} - {{ $currency->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="new_is_active" name="is_active"
                                    value="1" checked>
                                <label class="custom-control-label" for="new_is_active">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Account Modal -->
    <div class="modal fade" id="editAccountModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Receiving Account</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="editAccountForm" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Account Type <span class="text-danger">*</span></label>
                            <select class="form-control" id="edit_account_type" name="account_type" required>
                                <option value="">Select Type</option>
                                <option value="bank">Bank Account</option>
                                <option value="usdt">USDT Wallet</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Bank Name</label>
                            <input type="text" class="form-control" id="edit_bank_name" name="bank_name">
                        </div>
                        <div class="form-group">
                            <label>Account Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_account_name" name="account_name" required>
                        </div>
                        <div class="form-group">
                            <label>Account Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_account_number" name="account_number" required>
                        </div>
                        <div class="form-group">
                            <label>Currency <span class="text-danger">*</span></label>
                            <select class="form-control" id="edit_currency" name="currency" required>
                                <option value="">Select Currency</option>
                                @foreach($currencies as $currency)
                                    <option value="{{ $currency->code }}">{{ $currency->code }} - {{ $currency->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="edit_is_active" name="is_active"
                                    value="1">
                                <label class="custom-control-label" for="edit_is_active">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('.edit-account-btn').click(function () {
                var id = $(this).data('id');
                var type = $(this).data('type');
                var bank = $(this).data('bank');
                var name = $(this).data('name');
                var number = $(this).data('number');
                var currency = $(this).data('currency');
                var active = $(this).data('active');

                $('#editAccountForm').attr('action', '/settings/accounts/' + id);
                $('#edit_account_type').val(type);
                $('#edit_bank_name').val(bank);
                $('#edit_account_name').val(name);
                $('#edit_account_number').val(number);
                $('#edit_currency').val(currency);
                $('#edit_is_active').prop('checked', active);
            });
        });
    </script>
@endsection