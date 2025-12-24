@extends('layouts.app')

@section('title', 'Roles - Money Changer Admin')

@section('content')
    <div
        class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom pb-3 pt-sm-3">
        <h1>Roles & Permissions</h1>
        @if(auth()->user()->hasPermission('manage_roles'))
            <a href="{{ route('roles.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Role
            </a>
        @endif
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Permissions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            <tr>
                                <td>{{ $role->name }}</td>
                                <td><code>{{ $role->slug }}</code></td>
                                <td>
                                    @foreach($role->permissions as $permission)
                                        <span class="badge badge-info">{{ $permission->name }}</span>
                                    @endforeach
                                </td>
                                @if(auth()->user()->hasPermission('manage_roles'))
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-primary"
                                                title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($role->slug !== 'super-admin')
                                                <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this role?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No roles found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection