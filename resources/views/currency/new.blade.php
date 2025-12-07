@extends('layouts.app')

@section('head')

<script type="text/javascript">

    $(document).ready(function() 
    {
        prepareLocale();

        $("#mainForm").attr("enabled",1);

        $("#mainForm").on('submit',(function(e){
            e.preventDefault();
            submitMainForm();
        }));

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
            url: "/ajax/currency/create",
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

    .heading 
    {
        font-size: 15px;
        font-weight: bold;
        margin-bottom: 1rem;
    }

    #entry_valid, #entry_non_valid 
    {
        display: none;
        float: right
    }

    legend
    {
        width:50px;
        padding:0 10px;
        border-bottom:none;
    }

    ul
    {
        padding-inline-start: 20px;
    }

    li
    {
        list-style-type: none;
    }

    input[type="checkbox"]
    {
        margin-right: 3px;
    }

</style>

@endsection

@section('content')

<!-- Breadcrumb -->
<ol class="breadcrumb">
    <li class="breadcrumb-item">{{ __('app.currency.breadcrumb.currency') }}</li>
    <li class="breadcrumb-item">{{ __('app.currency.breadcrumb.currency.create') }}</li>
</ol>

<div class="container-fluid">
    <div class="animated fadeIn">

        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Add New Currency</h5>
                </div>

                <div class="card-body row">

                    <div class="col-md-6">
                        <form id="mainForm" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="code" class="form-label fw-bold">Currency Code</label>
                                <input type="text" id="code" name="code" class="form-control" placeholder="e.g. MYR" value="" required>
                                <small class="text-muted">Use 3-letter ISO code (MYR, USD, SGD, etc.)</small>
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label fw-bold">Currency Name</label>
                                <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Malaysia Ringgit" value="" required>
                            </div>

                            <div class="mb-3">
                                <label for="symbol" class="form-label fw-bold">Symbol</label>
                                <input type="text" id="symbol" name="symbol" class="form-control" placeholder="e.g. RM" value="">
                            </div>

                            <div class="mb-3">
                                <label for="buy_rate" class="form-label fw-bold">Buy Rate</label>
                                <input type="number" step="0.0001" id="buy_rate" name="buy_rate" class="form-control" placeholder="e.g. 4.7800" value="" min="0" required>
                                <small class="text-muted">
                                    Rate is based on: <strong>1 System Currency : X Foreign Currency</strong>
                                </small>
                            </div>

                            <div class="mb-3">
                                <label for="sell_rate" class="form-label fw-bold">Sell Rate</label>
                                <input type="number" step="0.0001" id="sell_rate" name="sell_rate" class="form-control" placeholder="e.g. 4.7800" value="" min="0" required>
                                <small class="text-muted">
                                    Rate is based on: <strong>1 System Currency : X Foreign Currency</strong>
                                </small>
                            </div>

                            <div class="mb-3">
                                <label for="status" class="form-label fw-bold">Status</label>
                                <select id="status" name="status" class="form-select form-control">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" id="btnSubmit" class="btn btn-primary px-4">
                                    <i class="bi bi-plus-circle"></i> Add Currency
                                </button>
                            </div>

                        </form>
                    </div>

                </div>

        </div>

    </div>
</div>

@endsection
