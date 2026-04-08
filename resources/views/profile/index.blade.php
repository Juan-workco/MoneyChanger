@extends('layouts.app')

@section('title', 'My Profile - Money Changer Admin')

@section('styles')
    <style>
        .alert.permanent-alert {
            display: block !important;
            animation: none !important;
            opacity: 1 !important;
        }
        /* Optional: Keep the animation smooth */
        @keyframes alert-fade {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center border-bottom pb-3 pt-sm-3">
        <h1><i class="fas fa-user-circle mr-2"></i> My Profile</h1>
    </div>

    <div class="row mt-4">
        {{-- Sidebar Info Card --}}
        <div class="col-md-3">
            <div class="card text-center mb-3">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-user-circle fa-5x text-secondary"></i>
                    </div>
                    <h5 class="card-title mb-1">{{ $user->name }}</h5>
                    <p class="text-muted small mb-1">{{ '@' . $user->username }}</p>
                    <span class="badge badge-{{ $user->assignedRole ? 'primary' : 'secondary' }}">
                        {{ $user->assignedRole->name ?? 'No Role' }}
                    </span>
                    <hr>
                    <div class="text-left small">
                        <p class="mb-1">
                            <i class="fas fa-envelope mr-1 text-muted"></i> {{ $user->email }}
                        </p>
                        <p class="mb-1">
                            <i class="fas fa-circle mr-1 {{ $user->status === 'active' ? 'text-success' : 'text-danger' }}"></i>
                            {{ ucfirst($user->status) }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Telegram Binding Status Card --}}
            <div class="card">
                <div class="card-header">
                    <i class="fab fa-telegram-plane mr-1"></i> Telegram Status
                </div>
                <div class="card-body text-center">
                    @if($user->telegram_active && $user->telegram_chat_id)
                        <span class="badge badge-success p-2 mb-2">
                            <i class="fas fa-link mr-1"></i> Linked
                        </span>
                        <p class="small text-muted mb-0">Chat ID: <code>{{ $user->telegram_chat_id }}</code></p>
                        <p class="small text-muted">Notifications are <strong>active</strong>.</p>
                    @elseif($user->telegram_active && !$user->telegram_chat_id)
                        <span class="badge badge-warning p-2 mb-2">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Pending Mapping
                        </span>
                        <p class="small text-muted">Username saved. Message the bot to complete linking.</p>
                    @else
                        <span class="badge badge-secondary p-2 mb-2">
                            <i class="fas fa-unlink mr-1"></i> Not Active
                        </span>
                        <p class="small text-muted mb-0">Enable Telegram below to receive notifications.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Main Form --}}
        <div class="col-md-9">
            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Personal Information --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-id-card mr-1"></i> Personal Information
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="username">Username</label>
                            <div class="col-md-9">
                                <input type="text" id="username" class="form-control-plaintext text-muted"
                                    value="{{ $user->username }}" readonly>
                                <small class="text-muted">Username cannot be changed.</small>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="name">Full Name</label>
                            <div class="col-md-9">
                                <input type="text" id="name" name="name" class="form-control @if ($errors->has('name')) is-invalid @endif"
                                    value="{{ old('name', $user->name) }}" required>
                                 
                                @if ($errors->has('name'))
                                    <div class="invalid-feedback d-block">{{ $errors->first('name') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="email">Email</label>
                            <div class="col-md-9">
                                <input type="email" id="email" name="email" class="form-control @if ($errors->has('email')) is-invalid @endif"
                                    value="{{ old('email', $user->email) }}" required>
                                 @if ($errors->has('email'))
                                     <div class="invalid-feedback d-block">{{ $errors->first('email') }}</div>
                                 @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Change Password --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-lock mr-1"></i> Change Password
                        <small class="text-muted ml-2">(Leave blank to keep current password)</small>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="password">New Password</label>
                            <div class="col-md-9">
                                <input type="password" id="password" name="password"
                                    class="form-control @if ($errors->has('password')) is-invalid @endif"
                                    placeholder="Enter new password (min. 6 characters)">
                                 @if ($errors->has('password'))
                                     <div class="invalid-feedback d-block">{{ $errors->first('password') }}</div>
                                 @endif
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="password_confirmation">Confirm Password</label>
                            <div class="col-md-9">
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                    class="form-control" placeholder="Repeat new password">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Telegram Notifications --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fab fa-telegram-plane mr-1"></i> Telegram Notifications
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info permanent-alert">
                            <i class="fas fa-info-circle mr-1"></i>
                            <strong>How to link your Telegram:</strong>
                            <ol class="mb-0 mt-1">
                                <li>Set your Telegram username below and enable notifications.</li>
                                <li>Save the profile.</li>
                                <li>Open Telegram, search for the system bot, and send <code>/start</code>.</li>
                                <li>Your account will be linked automatically — the status card on the left will update.</li>
                            </ol>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="telegram_username">Telegram Username</label>
                            <div class="col-md-9">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fab fa-telegram-plane"></i></span>
                                    </div>
                                    <input type="text" id="telegram_username" name="telegram_username"
                                        class="form-control @if ($errors->has('telegram_username')) is-invalid @endif"
                                        placeholder="e.g. johndoe (without @)"
                                        value="{{ old('telegram_username', ltrim($user->telegram_username ?? '', '@')) }}">
                                    @if ($errors->has('telegram_username'))
                                        <div class="invalid-feedback d-block">{{ $errors->first('telegram_username') }}</div>
                                    @endif
                                </div>
                                <small class="text-muted">Enter your Telegram username without the @ symbol.</small>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">Notifications</label>
                            <div class="col-md-9 d-flex align-items-center">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="telegram_active"
                                        name="telegram_active"
                                        {{ old('telegram_active', $user->telegram_active) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="telegram_active">
                                        Enable Telegram Notifications
                                    </label>
                                </div>
                            </div>
                        </div>

                        @if($user->telegram_chat_id)
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label">Linked Chat ID</label>
                                <div class="col-md-9 d-flex align-items-center">
                                    <code class="mr-2">{{ $user->telegram_chat_id }}</code>
                                    <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Mapped</span>
                                    <small class="text-muted ml-2">(Auto-updated when you message the bot)</small>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="d-flex justify-content-end mb-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Save Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
