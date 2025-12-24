@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <strong>Edit User</strong>
                </div>
                <div class="card-body">
                    <form action="{{ route('users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="username">Username</label>
                            <div class="col-md-9">
                                <input type="text" id="username" name="username" class="form-control"
                                    placeholder="Enter username" value="{{ old('username', $user->username) }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="name">Name</label>
                            <div class="col-md-9">
                                <input type="text" id="name" name="name" class="form-control" placeholder="Enter full name"
                                    value="{{ old('name', $user->name) }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="email">Email</label>
                            <div class="col-md-9">
                                <input type="email" id="email" name="email" class="form-control" placeholder="Enter email"
                                    value="{{ old('email', $user->email) }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="password">Password</label>
                            <div class="col-md-9">
                                <input type="password" id="password" name="password" class="form-control"
                                    placeholder="Leave blank to keep current password">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="password_confirmation">Confirm Password</label>
                            <div class="col-md-9">
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                    class="form-control" placeholder="Confirm password">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="role_id">Role</label>
                            <div class="col-md-9">
                                <select id="role_id" name="role_id" class="form-control" required>
                                    <option value="">Select Role</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="commission_rate">Commission Rate (%)</label>
                            <div class="col-md-9">
                                <input type="number" id="commission_rate" name="commission_rate" class="form-control"
                                    placeholder="Enter commission rate (e.g. 10.00)"
                                    value="{{ old('commission_rate', $user->commission_rate) }}" step="0.01" min="0"
                                    max="100">
                                <small class="text-muted">Percentage of profit allocated to agent.</small>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="status">Status</label>
                            <div class="col-md-9">
                                <select id="status" name="status" class="form-control" required>
                                    <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>
                                        Active</option>
                                    <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-dot-circle-o"></i>
                                Update</button>
                            <a href="{{ route('users.index') }}" class="btn btn-sm btn-danger"><i class="fa fa-ban"></i>
                                Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection