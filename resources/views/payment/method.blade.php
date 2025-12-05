@extends('layouts.app')

@section('head')
<script type="text/javascript">

$(document).ready(function()
{
    prepareLocale();

    utils.createSpinner("main-spinner");

    $("#addForm").attr("enabled",0);

    getMainData();

    $("#addForm").on('submit',(function(e){
        e.preventDefault();
        submitAddModal();
    }));

    $("#confirmationForm").on('submit',(function(e){
        e.preventDefault();
        submitStatusChangeModal();
    }));
});


function prepareLocale()
{
    locale['name'] = "Name";
    locale['status'] = "Status";
    locale['activate'] = "Activate";
    locale['deactivate'] = "Deactivate";

    locale['action'] = "{!! __('common.maindata.action') !!}";
    locale['edit'] = "{!! __('common.maindata.edit') !!}";
}

var mainData;
var refreshMainData = false;

function getMainData()
{
    $("#main-spinner").show();
    $("#main-table").hide();
    $('#notes').hide();

    var containerId = "main-table";

    var data = utils.getDataTableDetails(containerId);

    $.ajax({
        type: "GET",
        url: "/ajax/payment/method/list",
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
        ["type", locale['name'], true, false],
        ["status_desc", locale['status'], false, false],
        ["", locale['action'], false, false]
    ];

    table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

    if(table != null)
    {
        $('#notes').show();
        
        let fieldAction = utils.getDataTableFieldIdx("",fields);

        for (let i = 1, row; row = table.rows[i]; i++)
        {
            let data = mainData.results[i - 1];
            let status = data['status'];

            let btnEdit = document.createElement("button");
            btnEdit.className = "btn btn-primary";
            btnEdit.rowId = i;
            btnEdit.onclick = showStatusConfirmationModal;
            btnEdit.innerHTML = (status == 1) ? locale['deactivate'] : locale['activate'];

            row.cells[fieldAction].innerHTML = '';
            row.cells[fieldAction].appendChild(btnEdit);
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
    // $("#name").val("");

    filterMainData();
}

function resetAddModal()
{
    $("#addForm #name").val('');
    $("#addForm #status").val(1);
}

function showAddModal()
{
    $("#addForm").attr("enabled",1);

    resetAddModal();

    $("#modalAdd").modal("show");
}

function submitAddModal() 
{
    $("#modalAdd").modal('hide');
    $("#addForm").attr("enabled",0);
        
    utils.startLoadingBtn("btnSubmit","addForm");

    $.ajax({
        url: "/ajax/payment/method/new",
        type: "POST",
        data:  new FormData($("#addForm")[0]),
        contentType: false,
        cache: false,
        processData:false,
        success: function(data)
        {
            utils.stopLoadingBtn("btnSubmit","addForm");

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

function showStatusConfirmationModal()
{
    let rowId = this.rowId;
    let data = mainData.results[rowId - 1];
    let id = data['id'];
    let status = data['status'];

    let desc = (status == 1) ? locale['deactivate'] : locale['activate'];

    $("#confirmationForm #id").val(id);
    $("#confirmationForm #change_status_desc").text(desc);

    $("#modalStatusConfirmation").modal('show');
}

function submitStatusChangeModal()
{
        
    utils.startLoadingBtn("btnConfirmationSubmit","confirmationForm");

    $.ajax({
        url: "/ajax/payment/method/update",
        type: "POST",
        data:  new FormData($("#confirmationForm")[0]),
        contentType: false,
        cache: false,
        processData:false,
        success: function(data)
        {
            $("#modalStatusConfirmation").modal('hide');
            
            utils.stopLoadingBtn("btnConfirmationSubmit","confirmationForm");

            var obj = JSON.parse(data);

            if(obj.status == 1)
            {
                utils.showModal(locale['info'],locale['success'],obj.status, onModalConfirmationDismiss);
            }
            else
            {
                utils.showModal(locale['error'],obj.error,obj.status, onModalConfirmationErrorDismiss);
            }
        },
        error: function(){}             
    }); 
}

function onModalConfirmationDismiss()
{
    window.location.reload();
}

function onModalConfirmationErrorDismiss()
{
    $("#modalStatusConfirmation").modal('show');
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
    <li class="breadcrumb-item">{{ __('app.payment.breadcrumb.payment') }}</li>
    <li class="breadcrumb-item">{{ __('app.payment.breadcrumb.payment.method.list') }}</li>
</ol>

<div class="container-fluid">
    <div class="animated fadeIn">
        <div class="card">

            <div class="card-body">
                <button class="btn btn-primary" onclick="showAddModal()">{{ __("common.maindata.add") }}</button>
            </div>

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
                <h4 class="modal-title">{{ __('app.payment.modal.payment.method.title.add') }}</h4>
                <button class="close" id="close" data-dismiss="modal">×</button>
            </div>
            <div class="modal-body">
                <form id="addForm">
                    @csrf

                    <input type="hidden" id="id" name="id">

                    <div class="mb-3">
                        <label for="name" class="form-label fw-bold">Currency Name</label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Cash, Crypto" value="" required>
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
                            <i class="bi bi-plus-circle"></i> {{ __("common.maindata.add") }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="modalStatusConfirmation" class="modal fade" role="dialog">
    <div class="modal-dialog modal-primary modal-md" role="document">
        <div class="modal-content">
            
            <form id="confirmationForm">
                @csrf

                <div class="modal-header">
                    <h4 class="modal-title">{{ __('app.payment.modal.payment.method.title.status_change') }}</h4>
                    <button class="close" id="close" data-dismiss="modal">×</button>
                </div>

                <div class="modal-body">
                        
                        <input type="hidden" id="id" name="id">

                        <div class="">
                            Confrim to 
                            <strong id="change_status_desc"></strong>
                            Payment Method?
                        </div>
                </div>

                <div class="modal-footer">
                    <div class="d-flex justify-content-end">
                        <button type="button" id="close" class="btn btn-secondary px-4 mr-2"  data-dismiss="modal">
                            <i class="bi bi-plus-circle"></i> {{ __("common.modal.cancel") }}
                        </button>

                        <button type="submit" id="btnConfirmationSubmit" class="btn btn-primary px-4">
                            <i class="bi bi-plus-circle"></i> {{ __("common.maindata.confirm") }}
                        </button>
                    </div>
                </div>
            
            </form>
        </div>
    </div>
</div>


@endsection
