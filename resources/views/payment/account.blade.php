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

    // $("#editForm").on('submit',(function(e){
    //     e.preventDefault();
    //     submitEditModal();
    // }));
});


function prepareLocale()
{
    locale['name'] = "Name";
    locale['status'] = "Status";
    locale['method'] = "Method"
    locale['account'] = "Account"

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
    data["method"] = $("#f_method").val();
    data["name"] = $("#f_name").val();
    data["value"] = $("#f_value").val();

    $.ajax({
        type: "GET",
        url: "/ajax/payment/account/list",
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
        ["method", locale['method'], true, false],
        ["name", locale['name'], false, false],
        ["value", locale['account'], false, false]
    ];

    table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

    if(table != null)
    {
        $('#notes').show();
        
        let fieldAction = utils.getDataTableFieldIdx("",fields);

        for (let i = 1, row; row = table.rows[i]; i++)
        {
            let data = mainData.results[i - 1];
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
    $("#method").val(0);
    $("#name").val("");
    $("#account").val("");

    filterMainData();
}

function resetAddModal()
{
    $("#addForm").attr("enabled",1);

    $("#addForm #method").val(0);
    $("#addForm #name").val('');
    $("#addForm #value").val(1);
}

function showAddModal()
{
    resetAddModal();

    $("#modalAdd").modal("show");
}

function submitAddModal() 
{
    $("#modalAdd").modal('hide');
    $("#addForm").attr("enabled",0);
        
    utils.startLoadingBtn("btnSubmit","addForm");

    $.ajax({
        url: "/ajax/payment/account/new",
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
    $("#modalAdd").modal('show');
    $("#addForm").attr("enabled",1);
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
    <li class="breadcrumb-item">{{ __('app.payment.breadcrumb.payment.account.list') }}</li>
</ol>

<div class="container-fluid">
    <div class="animated fadeIn">

        <div class="card">

            <form method="POST" id="filterForm">

                <div class="card-header" style="display:block;">
                    <strong>{{ __('common.filter.title') }}</strong>
                </div>

                <div class="card-body">
                    
                    <div class="row">
                        <div class="col-sm-2">
                            
                            <div class="form-group">
                                <label for="f_method">{{ __('app.payment.filter.method') }}</label>
                                {{-- <input type="text" class="form-control form-control-sm ml-2" id="name" placeholder=""> --}}
                                <select name="method" id="f_method" class="form-control form-control-sm">
                                    <option value="0">All</option>
                                    @foreach ($methods as $opt)
                                        <option value="{{ $opt->id }}">{{ $opt->type }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-sm-2">
                            <div class="form-group">
                                <label for="f_name">{{ __('app.payment.filter.name') }}</label>
                                <input type="text" class="form-control form-control-sm" id="f_name" name="name" placeholder="">
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="f_value">{{ __('app.payment.filter.account') }}</label>
                                <input type="text" class="form-control form-control-sm" id="f_value" name="value" placeholder="">
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
                        <button class="btn btn-sm btn-primary" onclick="showAddModal()">{{ __("common.maindata.add") }}</button>
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
            
            <form id="addForm">
                @csrf

                <div class="modal-header">
                    <h4 class="modal-title">{{ __('app.payment.modal.payment.account.title.add') }}</h4>
                    <button class="close" id="close" data-dismiss="modal">×</button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label for="method">Method:</label>
                        <select name="method" id="method" class="form-control">
                            @foreach ($methods as $opt)
                                <option value="{{ $opt->id }}">{{ $opt->type }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" class="form-control" id="name" placeholder="Name" name="name">
                    </div>

                    <div class="form-group">
                        <label for="value">Account:</label>
                        <input type="text" class="form-control" id="value" placeholder="Account" name="value">
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="d-flex justify-content-end">
                        <button type="submit" id="btnSubmit" class="btn btn-primary px-4">
                            <i class="bi bi-plus-circle"></i> {{ __("common.maindata.add") }}
                        </button>
                    </div>
                </div>
            
            </form>
        </div>
    </div>
</div>

{{-- <div id="modalEdit" class="modal fade" role="dialog">
    <div class="modal-dialog modal-primary modal-md" role="document">
        <div class="modal-content">
            
            <form id="editForm">
                @csrf

                <div class="modal-header">
                    <h4 class="modal-title">{{ __('app.payment.modal.payment.account.title.edit') }}</h4>
                    <button class="close" id="close" data-dismiss="modal">×</button>
                </div>

                <div class="modal-body">
                        
                </div>

                <div class="modal-footer">
                    <div class="d-flex justify-content-end">
                        <button type="button" id="close" class="btn btn-secondary px-4 mr-2"  data-dismiss="modal">
                            <i class="bi bi-plus-circle"></i> {{ __("common.modal.cancel") }}
                        </button>

                        <button type="submit" id="btnEditSubmit" class="btn btn-primary px-4">
                            <i class="bi bi-plus-circle"></i> {{ __("common.maindata.edit") }}
                        </button>
                    </div>
                </div>
            
            </form>
        </div>
    </div>
</div> --}}


@endsection
