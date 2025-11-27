@extends('layouts.app')

@section('head')
<script type="text/javascript">

$(document).ready(function()
{
    prepareLocale();

    utils.createSpinner("main-spinner");

    $("#editForm").attr("enabled",0);

    getMainData();

    $("#editForm").on('submit',(function(e){
        e.preventDefault();
        submitEditModal();
    }));
});


function prepareLocale()
{
    locale['code'] = "Code";
    locale['name'] = "Name";
    locale['symbol'] = "Symbol";
    locale['buy_rate'] = "Buy Rate";
    locale['sell_rate'] = "Sell Rate";
    locale['status'] = "Status";

    locale['action'] = "{!! __('common.maindata.action') !!}";
    locale['edit'] = "{!! __('common.maindata.edit') !!}";
}

var mainData;
var refreshMainData = false;

function getMainData()
{
    var containerId = "main-table";

    $("#main-spinner").show();
    $("#main-table").hide();
    $('#notes').hide();

    var data = utils.getDataTableDetails(containerId);

    var errorCheck = 0;

    $.ajax({
        type: "GET",
        url: "/ajax/currency/list",
        data: data,
        success: function(data)
        {
            let obj = JSON.parse(data);

            if(obj.status = 1)
            {
                mainData = obj.data
            }
            else
            {
                mainData = [];
            }

            loadMainData(containerId);
        }
    });
}

function loadMainData(containerId)
{
    $("#main-spinner").hide();
    $("#main-table").show();

    let fields = [
        ["code", locale['code'], true, false],
        ["name", locale['name'], true, false],
        ["symbol", locale['symbol'], false, false],
        ["status_desc", locale['status'], true, false],
        ["buy_rate", locale['buy_rate'], false, true],
        ["sell_rate", locale['sell_rate'], false, true],
        ["", locale['action'], false, false]
    ];

    table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

    if(table != null)
    {
        $('#notes').show();

        let fieldBuyRate = utils.getDataTableFieldIdx("buy_rate",fields);
        let fieldSellRate = utils.getDataTableFieldIdx("sell_rate",fields);
        let fieldAction = utils.getDataTableFieldIdx("",fields);

        for (let i = 1, row; row = table.rows[i]; i++)
        {
            let buyRate = mainData.results[i - 1]["buy_rate"];
            let sellRate = mainData.results[i - 1]["sell_rate"];

            row.cells[fieldBuyRate].innerHTML = utils.formatMoney(buyRate, 4);
            row.cells[fieldSellRate].innerHTML = utils.formatMoney(sellRate, 4);

            let editBtn = document.createElement("button");
            editBtn.className = "btn btn-primary";
            editBtn.rowId = i;
            editBtn.onclick = showEditModal;
            editBtn.innerHTML = locale['edit'];

            row.cells[fieldAction].innerHTML = "";
            row.cells[fieldAction].appendChild(editBtn);
        }
    }
}

function sortMainData()
{
    utils.prepareDataTableSortData(this.containerId,this.orderBy);

    getMainData();
}

function pagingMainData()
{
    utils.prepareDataTablePagingData(this.containerId,this.page);

    getMainData();
}

function filterMainData()
{
    utils.resetDataTableDetails("main-table");

    getMainData();
}

function resetMainData()
{
    $("#code").val("");
    $("#name").val("");

    filterMainData();
}

function showEditModal()
{
    $("#editForm").attr("enabled",1);

    let rowId = this.rowId;
    let data = mainData.results[rowId - 1];
    let id = data['id'];
    let code = data['code'];
    let name = data['name'];
    let symbol = data['symbol'];
    let buyRate = data['buy_rate'];
    let sellRate = data['sell_rate'];
    let status = data['status'];

    $("#modalEdit #id").val(id);
    $("#modalEdit #code").val(code);
    $("#modalEdit #name").val(name);
    $("#modalEdit #symbol").val(symbol);
    $("#modalEdit #buy_rate").val(utils.formatMoney(buyRate, 4));
    $("#modalEdit #sell_rate").val(utils.formatMoney(sellRate, 4));
    $("#modalEdit #status").val(status);

    $("#modalEdit").modal("show");

    $("#modalEdit").on("hide.bs.modal", () => {
        $("#editForm").attr("enabled",0);
    })
}

function submitEditModal() 
{
    $("#modalEdit").modal('hide');
    $("#editForm").attr("enabled",0);
        
    utils.startLoadingBtn("btnSubmit","editForm");

    $.ajax({
        url: "/ajax/currency/edit",
        type: "POST",
        data:  new FormData($("#editForm")[0]),
        contentType: false,
        cache: false,
        processData:false,
        success: function(data)
        {
            utils.stopLoadingBtn("btnSubmit","editForm");

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
    filterMainData();
}

function onMainModalDismissError()
{
    $("#modalEdit").modal('show');
    $("#editForm").attr("enabled",1);
}

</script>

<style type="text/css">

    table, th, td
    {
      border: 1px solid black;
      border-collapse: collapse;
    }

    th, td
    {
      padding: 5px;
    }

    .fields
    {
      font-weight: bolder;
    }

    .border-less
    {
        border-top: 1px solid #FFFFFF;
    }

</style>

@endsection

@section('content')

<!-- Breadcrumb -->
<ol class="breadcrumb">
    <li class="breadcrumb-item">{{ __('app.currency.breadcrumb.currency') }}</li>
    <li class="breadcrumb-item">{{ __('app.currency.breadcrumb.currency.list') }}</li>
</ol>

<div class="container-fluid">
    <div class="animated fadeIn">

        <div class="card">

            <form method="POST" id="filterForm">

                <div class="card-header">
                    <strong>{{ __('common.filter.title') }}</strong>
                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-sm-2">
                            <div class="form-group">
                                <label for="code">{{ __('common.filter.currency.code') }}</label>
                                <input type="text" class="form-control" name="code" id="code" placeholder="MYR" autocomplete="off">
                            </div>
                        </div>

                        <div class="col-sm-2">
                            <div class="form-group">
                                <label for="name">{{ __('common.filter.name') }}</label>
                                <input type="text" class="form-control" name="name" id="name" placeholder="Malaysia Ringgit" autocomplete="off">
                            </div>
                        </div>
                    </div>

                </div>

                <div class="card-footer">
                    <button type="button" id="submit" class="btn btn-sm btn-success" onclick="filterMainData()"><i class="fa fa-dot-circle-o"></i> {{ __('common.filter.submit') }}</button>

                    <button type="button" class="btn btn-sm btn-danger" onclick="resetMainData()"><i class="fa fa-ban"></i> {{ __('common.filter.reset') }}</button>
                </div>

            </form>

        </div>

        <div class="card">

            <div id="main-spinner" class="card-body"></div>

            <div id="main-table" class="card-body"></div>

            <div id="notes" class="card-body">{{ __('common.notes.timezone') }}</div>

        </div>
    </div>
</div>

<div id="modalEdit" class="modal fade" role="dialog">
    <div class="modal-dialog modal-primary modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('app.currency.modal.title.edit') }}</h4>
                <button class="close" id="close" data-dismiss="modal">×</button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    @csrf

                    <input type="hidden" id="id" name="id">

                    <div class="mb-3">
                        <label for="code" class="form-label fw-bold">Currency Code</label>
                        <input type="text" id="code" name="code" class="form-control" placeholder="e.g. MYR" value="">
                        <small class="text-muted">Use 3-letter ISO code (MYR, USD, SGD, etc.)</small>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label fw-bold">Currency Name</label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Malaysia Ringgit" value="" required>
                    </div>

                    <div class="mb-3">
                        <label for="symbol" class="form-label fw-bold">Symbol</label>
                        <input type="text" id="symbol" name="symbol" class="form-control" placeholder="e.g. RM" value="" required>
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
                            <i class="bi bi-plus-circle"></i> {{ __("common.maindata.edit") }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


@endsection
