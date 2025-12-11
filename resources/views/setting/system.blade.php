@extends('layouts.app')

@section('head')

<script type="text/javascript">
    const settings = @json($settings);

    $(document).ready(function() 
    {
        prepareLocale();

        $("#mainForm").attr("enabled",1);

        $("#mainForm").on('submit',(function(e){
            e.preventDefault();
            submitMainForm();
        }));

        settings.forEach(setting => {
            const {setting_key, setting_value} = setting;
            
            $(`#${setting_key}`).val(setting_value);
        })

    });

    function prepareLocale()
    {
        locale['info'] = "{!! __('common.modal.info') !!}";
        locale['success'] = "{!! __('common.modal.success') !!}";
        locale['error'] = "{!! __('common.modal.error') !!}";
    }

    function submitMainForm()
    {   
        if($("#mainForm").attr("enabled") == 0)
        {
            return;
        }
        
        utils.startLoadingBtn("btnSubmit","mainForm");

        $.ajax({
            url: "/ajax/setting/system/update",
            type: "POST",
            data:  new FormData($("#mainForm")[0]),
            contentType: false,
            cache: false,
            processData:false,
            success: function(data)
            {
                utils.stopLoadingBtn("btnSubmit","mainForm");

                var obj = JSON.parse(data);

                if(obj.status == 1)
                {
                    utils.showModal(locale['info'],locale['success'],obj.status,onMainModalDismiss);
                }
                else
                {
                    utils.showModal(locale['error'],obj.error,obj.status,onMainModalDismissError);
                }
            },
            error: function(){}             
        }); 
    }

    function onMainModalDismiss()
    {
        window.location.reload();
    }

    function onMainModalDismissError()
    {
        $("#mainForm").attr("enabled",1);
    }
</script>

<style>

</style>

@endsection

@section('content')

<!-- Breadcrumb -->
<ol class="breadcrumb">
    <li class="breadcrumb-item">{{ __('app.setting.breadcrumb.setting') }}</li>
    <li class="breadcrumb-item">{{ __('app.setting.breadcrumb.setting.system') }}</li>
</ol>

<div class="container-fluid">
    <div class="animated fadeIn">

        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">System Settings</h5>
                </div>

                <div class="card-body row">

                    <div class="col-md-6">
                        <form id="mainForm" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="currency_id" class="form-label fw-bold">System Currency</label>
                                <select name="currency_id" id="currency_id" class="form-control">
                                    @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->code }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" id="btnSubmit" class="btn btn-primary px-4">
                                    <i class="bi bi-plus-circle"></i> Update
                                </button>
                            </div>

                        </form>
                    </div>

                </div>

        </div>

    </div>
</div>

@endsection
