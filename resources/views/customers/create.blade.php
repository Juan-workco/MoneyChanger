@extends('layouts.app')

@section('title', 'Add Customer - Money Changer Admin')

@section('content')
    <div class="page-header">
        <h1>Add New Customer</h1>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('customers.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-3">Personal Information</h5>

                                <div class="form-group">
                                    <label for="name">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                        id="name" name="name" value="{{ old('name') }}" required>
                                    @if ($errors->has('name'))
                                        <div class="invalid-feedback">{{ $errors->first('name') }}</div>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="group_name">Group Name</label>
                                    <select class="form-control group-name-select {{ $errors->has('group_name') ? 'is-invalid' : '' }}" 
                                        id="group_name" 
                                        name="group_name">
                                        <option value=""></option>
                                        @foreach($existingGroups as $group)
                                            <option value="{{ $group }}" {{ old('group_name') == $group ? 'selected' : '' }}>{{ $group }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Select from existing groups or type a new one</small>
                                    @if ($errors->has('group_name'))
                                        <div class="invalid-feedback d-block">{{ $errors->first('group_name') }}</div>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="contact_info">Contact Info</label>
                                    <input type="text" 
                                        class="form-control {{ $errors->has('contact_info') ? 'is-invalid' : '' }}" 
                                        id="contact_info" 
                                        name="contact_info" 
                                        value="{{ old('contact_info') }}"
                                        placeholder="e.g., WeChat: john123, WhatsApp: +60123456789">
                                    <small class="text-muted">Enter contact details (WeChat, WhatsApp, Telegram, etc.)</small>
                                    @if ($errors->has('contact_info'))
                                        <div class="invalid-feedback">{{ $errors->first('contact_info') }}</div>
                                    @endif
                                </div>

                                @if($canManageUplines)
                                <div class="form-group">
                                    <label for="upline1_id">Upline 1 <span class="text-muted">(Optional)</span></label>
                                    <select class="form-control {{ $errors->has('upline1_id') ? 'is-invalid' : '' }}" 
                                        id="upline1_id" 
                                        name="upline1_id">
                                        <option value="">-- Select Upline 1 --</option>
                                        @foreach($agents as $agent)
                                            <option value="{{ $agent->id }}" {{ old('upline1_id') == $agent->id ? 'selected' : '' }}>
                                                {{ $agent->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('upline1_id'))
                                        <div class="invalid-feedback d-block">{{ $errors->first('upline1_id') }}</div>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="upline2_id">Upline 2 <span class="text-muted">(Optional)</span></label>
                                    <select class="form-control {{ $errors->has('upline2_id') ? 'is-invalid' : '' }}" 
                                        id="upline2_id" 
                                        name="upline2_id">
                                        <option value="">-- Select Upline 2 --</option>
                                        @foreach($agents as $agent)
                                            <option value="{{ $agent->id }}" {{ old('upline2_id') == $agent->id ? 'selected' : '' }}>
                                                {{ $agent->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('upline2_id'))
                                        <div class="invalid-feedback d-block">{{ $errors->first('upline2_id') }}</div>
                                    @endif
                                </div>
                                @endif

                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                        id="email" name="email" value="{{ old('email') }}">
                                    @if ($errors->has('email'))
                                        <div class="invalid-feedback">{{ $errors->first('email') }}</div>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="phone">Phone Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control {{ $errors->has('phone') ? 'is-invalid' : '' }}"
                                        id="phone" name="phone" value="{{ old('phone') }}" required>
                                    @if ($errors->has('phone'))
                                        <div class="invalid-feedback">{{ $errors->first('phone') }}</div>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <textarea class="form-control {{ $errors->has('address') ? 'is-invalid' : '' }}"
                                        id="address" name="address" rows="3">{{ old('address') }}</textarea>
                                    @if ($errors->has('address'))
                                        <div class="invalid-feedback">{{ $errors->first('address') }}</div>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="country">Country</label>
                                    <input type="text"
                                        class="form-control {{ $errors->has('country') ? 'is-invalid' : '' }}" id="country"
                                        name="country" value="{{ old('country') }}">
                                    @if ($errors->has('country'))
                                        <div class="invalid-feedback">{{ $errors->first('country') }}</div>
                                    @endif
                                </div>                                

                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active"
                                            value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_active">Active Customer</label>
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>

                        {{-- Commission Configuration Section --}}
                        @if($canManageUplines)
                        <div class="card-body row mt-4">
                            <div class="col-12">
                                <h5 class="text-muted mb-3"><i class="fas fa-percentage"></i> Commission Points Configuration</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-sm">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width: 30%">Currency Pair</th>
                                                <th style="width: 35%">Upline 1 Point</th>
                                                <th style="width: 35%">Upline 2 Point</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if($currencyPairs->isEmpty())
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted">No active currency pairs found.</td>
                                                </tr>
                                            @else
                                                @foreach($currencyPairs as $pair)
                                                <tr>
                                                    <td class="align-middle font-weight-bold">
                                                        {{ $pair->name }} 
                                                        <small class="text-muted d-block">Default: {{ number_format($pair->default_point, 4) }}</small>
                                                    </td>
                                                    <td style="vertical-align: middle;">
                                                        <input type="number" step="0.0001" class="form-control form-control-sm" 
                                                            name="commissions[{{ $pair->id }}][upline1]" 
                                                            placeholder="Default: {{ $pair->default_point }}"
                                                            value="{{ old("commissions.{$pair->id}.upline1") }}">
                                                    </td>
                                                    <td style="vertical-align: middle;">
                                                        <input type="number" step="0.0001" class="form-control form-control-sm" 
                                                            name="commissions[{{ $pair->id }}][upline2]" 
                                                            placeholder="Default: {{ $pair->default_point }}"
                                                            value="{{ old("commissions.{$pair->id}.upline2") }}">
                                                    </td>
                                                </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i> 
                                    Leave blank to use the default system point. Enter specific value (e.g. 0.02) to override.
                                </small>
                            </div>
                        </div>
                        @endif

                        <div class="card-body mt-4 pt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Customer
                            </button>
                            <a href="{{ route('customers.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2 for group name with tags (allows typing new values)
        $('.group-name-select').select2({
            theme: 'bootstrap4',
            tags: true,
            placeholder: 'Select or type new group...',
            allowClear: true
        });

        // Store all agents for filtering
        var allAgents = @json($agents);

        // Function to update upline dropdowns
        function updateUplineOptions() {
            var upline1Value = $('#upline1_id').val();
            var upline2Value = $('#upline2_id').val();

            // Update Upline 2 options (exclude selected Upline 1)
            var upline2Select = $('#upline2_id');
            var currentUpline2 = upline2Select.val();
            upline2Select.empty();
            upline2Select.append('<option value="">-- Select Upline 2 --</option>');
            allAgents.forEach(function(agent) {
                if (agent.id != upline1Value) {
                    var selected = agent.id == currentUpline2 ? 'selected' : '';
                    upline2Select.append('<option value="' + agent.id + '" ' + selected + '>' + agent.name + '</option>');
                }
            });

            // Update Upline 1 options (exclude selected Upline 2)
            var upline1Select = $('#upline1_id');
            var currentUpline1 = upline1Select.val();
            upline1Select.empty();
            upline1Select.append('<option value="">-- Select Upline 1 --</option>');
            allAgents.forEach(function(agent) {
                if (agent.id != upline2Value) {
                    var selected = agent.id == currentUpline1 ? 'selected' : '';
                    upline1Select.append('<option value="' + agent.id + '" ' + selected + '>' + agent.name + '</option>');
                }
            });
        }

        // Listen for changes on both upline dropdowns
        $('#upline1_id, #upline2_id').on('change', function() {
            updateUplineOptions();
        });

        // Initial update on page load
        updateUplineOptions();
    });
</script>
@endsection