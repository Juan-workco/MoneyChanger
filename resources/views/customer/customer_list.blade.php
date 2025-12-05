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
    locale['total_transaction'] = "Total Transaction";
    locale['created_at'] = "Created At";

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
        url: "/ajax/customer/list",
        data: data,
        success: function(data)
        {
            let obj = JSON.parse(data);

            if(obj.status == 1)
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
        ["name", locale['code'], true, false],
        ["phone", locale['name'], true, false],
        ["active_desc", locale['status'], false, false],
        ["total_transactions", locale['total_transaction'], true, true],
        ["created_at", locale['created_at'], false, true],
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

function showAddModal()
{
    $("#modalAdd").modal("show");
}

function submitAddModal()
{
    utils.startLoadingBtn("btnSubmitAdd","modalAdd");

    $.ajax({
        url: "/ajax/customer/new",
        type: "POST",
        data:  new FormData($("#addForm")[0]),
        contentType: false,
        cache: false,
        processData:false,
        success: function(data)
        {
            utils.stopLoadingBtn("btnSubmitAdd","modalAdd");

            $("#modalAdd").modal("hide");

            var obj = JSON.parse(data);

            if(obj.status == 1)
            {
                utils.showModal(locale['info'],locale['success'],obj.status,onAddModalDismiss);
            }
            else
            {
                utils.showModal(locale['error'],obj.error,obj.status,onAddModalErrorDismiss);
            }
        },
        error: function(){}             
    }); 
}

function onAddModalDismiss()
{
    $("#addForm")[0].reset();
    filterMainData();
}

function onAddModalErrorDismiss()
{
    $("#modalAdd").modal("show");
}

function showEditModal()
{
    $("#editForm").attr("enabled",1);

    let rowId = this.rowId;
    let data = mainData.results[rowId - 1];
    let id = data['id'];
    let name = data['name'];
    let phone = data['phone'];
    let status = data['status'];

    $("#modalEdit #id").val(id);
    $("#modalEdit #name").val(name);
    $("#modalEdit #phone").val(phone);
    $("#modalEdit #status").val(status);

    $("#modalEdit").modal("show");

    $("#modalEdit").on("hide.bs.modal", () => {
        $("#editForm").attr("enabled",0);
    })
}

function submitEditModal() 
{
    utils.startLoadingBtn("btnSubmitEdit","modalEdit");

    $.ajax({
        url: "/ajax/customer/edit",
        type: "POST",
        data:  new FormData($("#editForm")[0]),
        contentType: false,
        cache: false,
        processData:false,
        success: function(data)
        {
            utils.stopLoadingBtn("btnSubmitEdit","modalEdit");

            $("#modalEdit").modal("hide");

            var obj = JSON.parse(data);

            if(obj.status == 1)
            {
                utils.showModal(locale['info'],locale['success'],obj.status,onEditModalDismiss);
            }
            else
            {
                utils.showModal(locale['error'],obj.error,obj.status,onEditModalDismissError);
            }
        },
        error: function(){}             
    }); 
}

function onEditModalDismiss()
{
    $("#editForm")[0].reset();
    filterMainData();
}

function onEditModalDismissError()
{
    $("#modalEdit").modal("show");
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
    <li class="breadcrumb-item">{{ __('app.currency.breadcrumb.customer') }}</li>
    <li class="breadcrumb-item">{{ __('app.currency.breadcrumb.customer.list') }}</li>
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
                                <label for="name">{{ __('app.customer.filter.name') }}</label>
                                <input type="text" class="form-control" id="name" autocomplete="off" placeholder="">
                            </div>
                        </div>

                        <div class="col-sm-2">
                            <div class="form-group">
                                <label for="phone">{{ __('app.customer.filter.phone') }}</label>
                                <input type="text" class="form-control" id="phone" autocomplete="off" placeholder="">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-between" style="gap: 2px">
                    <div>
                        <button type="button" class="btn btn-sm btn-success" onclick="filterMainData()"><i class="fa fa-dot-circle-o"></i> {{ __('common.filter.submit') }}</button>

                        <button type="button" class="btn btn-sm btn-danger" onclick="resetMainData()"><i class="fa fa-ban"></i> {{ __('common.filter.reset') }}</button>
                    </div>
                    
                    <div>
                        <button type="button" class="btn btn-sm btn-primary" onclick="showAddModal()">{{ __("common.maindata.add") }}</button>
                    </div>
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

<div id="modalAdd" class="modal fade" role="dialog">
    <div class="modal-dialog modal-primary modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('app.currency.modal.title.add') }}</h4>
                <button class="close" id="close" data-dismiss="modal">×</button>
            </div>
            <div class="modal-body">
                <form id="addForm">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label fw-bold">Name</label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="Customer Name" value="" required>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label fw-bold">Phone</label>
                        <input type="text" id="phone" name="phone" class="form-control" placeholder="Customer Phone" value="">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div class="d-flex justify-content-end">
                    <button type="submit" id="btnSubmitAdd" class="btn btn-primary px-4" onclick="submitAddModal()">
                        <i class="bi bi-plus-circle"></i> {{ __("common.maindata.submit") }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modalEdit" class="modal fade" role="dialog">
    <div class="modal-dialog modal-primary modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('app.customer.modal.title.edit') }}</h4>
                <button class="close" id="close" data-dismiss="modal">×</button>
            </div>

            <div class="modal-body">
                <form id="editForm">
                    @csrf

                    <input type="hidden" id="id" name="id">

                    <div class="mb-3">
                        <label for="Name" class="form-label fw-bold">Name</label>
                        <input type="text" id="name" name="name" class="form-control" value="" required>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label fw-bold">Phone</label>
                        <input type="text" id="phone" name="phone" class="form-control" value="">
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label fw-bold" class="form-control">Status</label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <div class="d-flex justify-content-end">
                    <button type="submit" id="btnSubmitEdit" class="btn btn-primary px-4" onclick="submitEditModal()">
                        <i class="bi bi-plus-circle"></i> {{ __("common.maindata.edit") }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
