@extends('layouts.app')

@section('title', 'Edit Role - Money Changer Admin')

@section('content')
    <div class="page-header">
        <h1>Edit Role: {{ $role->name }}</h1>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('roles.update', $role->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="name">Role Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" 
                                id="name" name="name" value="{{ old('name', $role->name) }}" required>
                            @if ($errors->has('name'))
                                <div class="invalid-feedback">{{ $errors->first('name') }}</div>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="slug">Slug <span class="text-danger">*</span></label>
                            <input type="text" class="form-control {{ $errors->has('slug') ? 'is-invalid' : '' }}" 
                                id="slug" name="slug" value="{{ old('slug', $role->slug) }}" required>
                            @if ($errors->has('slug'))
                                <div class="invalid-feedback">{{ $errors->first('slug') }}</div>
                            @endif
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Permissions</label>
                            <hr class="mt-0">
                            @foreach($permissions as $module => $items)
                                <div class="mb-4">
                                    <h6 class="text-primary font-weight-bold"><i class="fas fa-folder-open mr-2"></i>{{ $module }}</h6>
                                    <div class="row pl-3">
                                        @foreach($items as $permission)
                                            <div class="col-md-4 mb-2">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" 
                                                        id="perm_{{ $permission->id }}" name="permissions[]" 
                                                        value="{{ $permission->id }}"
                                                        {{ $role->permissions->contains($permission->id) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="perm_{{ $permission->id }}">
                                                        {{ str_replace($module, '', $permission->name) ?: $permission->name }}
                                                        <small class="text-muted d-block">{{ $permission->description }}</small>
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Role
                            </button>
                            <a href="{{ route('roles.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
