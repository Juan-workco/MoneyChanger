@extends('layouts.app')

@section('head')

<script type="text/javascript">
    const currencies = @json($currencies);
    const defaultCurrency = @json($defaultCurrency);

    $(document).ready(function() 
    {
        prepareLocale();

        let currency = currencies.find(currency => currency.id == defaultCurrency);

        $("#from_currency").val(currency.code);

        handleExchangCurrencyChange($("#to_currency")[0]);

        utils.formatCurrencyInput($("#from_amount, #to_amount"));

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

        let fromAmount = $('#from_amount').val();
        let toAmount = $('#to_amount').val();

        let data = new FormData($("#mainForm")[0]);
        data.set('from_currency', defaultCurrency);
        data.set('from_amount', utils.formatFloat(fromAmount));
        data.set('to_amount', utils.formatFloat(toAmount));

        $.ajax({
            url: "/ajax/remittance/new",
            type: "POST",
            data:  data,
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

    function handleExchangCurrencyChange(el)
    {
        let rate = currencies.find(c => c.id == el.value);
        rate = rate.rate;

        $("#exchange_rate").val(utils.formatMoney(rate));
    }

    function handleAmountChange(el)
    {
        let isFromEL = (el.id === 'from_amount') ? true : false;
        let exchangeId = $("#to_currency").val();
        let currency = currencies.find(c => c.id == exchangeId);
        rate = currency.rate;

        let amount = utils.formatFloat(el.value);

        if (isFromEL)
        {
            let changeAmount = amount * rate;
            $("#to_amount").val(utils.formatMoney(changeAmount))
        }
        else
        {
            let changeAmount = amount * rate;
            $("#from_amount").val(utils.formatMoney(changeAmount))
        }
    }
</script>

<style>

    #from_amount::-webkit-outer-spin-button,
    #from_amount::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* For Firefox */
    #from_amount {
        -moz-appearance: textfield;
    }

</style>

@endsection

@section('content')

<!-- Breadcrumb -->
<ol class="breadcrumb">
    <li class="breadcrumb-item">{{ __('app.remittance.breadcrumb.remittance') }}</li>
    <li class="breadcrumb-item">{{ __('app.remittance.breadcrumb.remittance.create') }}</li>
</ol>

<div class="container-fluid">
    <div class="animated fadeIn">

        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Add New Remittance</h5>
                </div>

                <div class="card-body row">

                    <div class="col-md-6">
                        <form id="mainForm" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="customer" class="form-label fw-bold">Customer</label>
                                <select name="customer" id="customer" class="form-control">
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-5">
                                    <div class="mb-3">
                                        <label for="from_currency" class="form-label fw-bold">From Currency</label>
                                        <input type="text" id="from_currency" class="form-control" class="form-control" value="" disabled>
                                    </div>

                                    
                                    <div class="mb-3">
                                        <label for="from_amount" class="form-label fw-bold">From Amount</label>
                                        <input type="text" id="from_amount" name="from_amount" placeholder="0" class="form-control" onchange="handleAmountChange(this)">
                                    </div>
                                </div>
                                
                                <div class="col-md-2 align-content-end">
                                    <input type="number" step="0" id="exchange_rate" name="exchange_rate" placeholder="0.00" class="form-control text-center mb-3" disabled>
                                </div>

                                <div class="col-md-5">
                                    <div class="mb-3">
                                        <label for="to_currency" class="form-label fw-bold">To Currency</label>
                                        <select name="to_currency" id="to_currency" class="form-control" onchange="handleExchangCurrencyChange(this)">
                                            @foreach ($currencies as $currency)
                                                @if ($currency->id != $defaultCurrency)
                                                    <option value="{{ $currency->id }}">{{ $currency->code }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="to_amount" class="form-label fw-bold">To Amount</label>
                                        <input type="text" id="to_amount" name="to_amount" placeholder="0" class="form-control" onchange="handleAmountChange(this)">
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" id="btnSubmit" class="btn btn-primary px-4">
                                    <i class="bi bi-plus-circle"></i> Add Remittance
                                </button>
                            </div>

                        </form>
                    </div>

                </div>

        </div>

    </div>
</div>

@endsection
