@extends('layouts.app')

@section('title', 'Telegram Settings - Money Changer Admin')

@section('content')
    <div
        class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom pb-3 pt-sm-3">
        <h1>Telegram Settings</h1>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="list-group">
                <a href="{{ route('settings.index') }}" class="list-group-item list-group-item-action">
                    <i class="fas fa-cog mr-2"></i> General
                </a>
                <a href="{{ route('settings.accounts') }}" class="list-group-item list-group-item-action">
                    <i class="fas fa-university mr-2"></i> Receiving Accounts
                </a>
                <a href="{{ route('settings.payment-methods') }}" class="list-group-item list-group-item-action">
                    <i class="fas fa-credit-card mr-2"></i> Payment Methods
                </a>
                <a href="{{ route('settings.telegram') }}" class="list-group-item list-group-item-action active">
                    <i class="fab fa-telegram-plane mr-2"></i> Telegram
                </a>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <i class="fab fa-telegram-plane"></i> Telegram Bot Configuration
                </div>
                <div class="card-body">
                    <form action="{{ route('settings.telegram.update') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="bot_token" class="">Bot Token</label>
                            <input type="text" class="form-control" id="bot_token" name="bot_token"
                                    value="{{ old('bot_token', $setting->bot_token) }}" required>
                            <small class="form-text text-muted">The token you get from @BotFather when creating your
                                Telegram
                                bot.
                            </small>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Per-User Notifications:</strong> Notifications are sent directly to each agent's personal Telegram chat.
                            Each agent must set their Telegram username in <a href="{{ route('profile.index') }}" class="alert-link">My Profile</a>
                            and then message the bot to complete the mapping automatically.
                        </div>

                        <div class="form-group">
                            <label for="is_active" class="">Status</label>
                            <div class="custom-control custom-switch mt-1">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" {{ old('is_active', $setting->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Enable Telegram Integration</label>
                            </div>
                        </div>

                        <hr>

                        <div class="form-group">
                            <label for="webhook_url" class="col-form-label"><strong>Webhook Target URL</strong></label>
                            <input type="text" readonly class="form-control-plaintext text-info"
                                value="{{ $setting->webhook_url ?? 'Will be generated automatically upon saving' }}">
                            <small class="form-text text-muted">The system will automatically register this webhook to Telegram upon saving.
                            </small>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Settings & Register Webhook
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
@endsection