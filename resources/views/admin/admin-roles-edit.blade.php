@extends('layouts.app')

@section('head')

<script type="text/javascript">

    $(document).ready(function() 
    {
        prepareLocale();

        getMainData();

        $("#mainForm").on('submit',(function(e){
            e.preventDefault();
            submitMainForm();
        }));
    });

    var check_old = [];

    function prepareLocale()
    {
        locale['info'] = "{!! __('common.modal.info') !!}";
        locale['success'] = "{!! __('common.modal.success') !!}";
        locale['error'] = "{!! __('common.modal.error') !!}";
    }

    function getMainData()
    {
        $("#mainForm").attr("enabled",1);

        var containerId = "card-body";

        $("#main-spinner").show();
        $("#main-table").hide();

        var data = {};

        data["id"] = utils.getParameterByName('id'); 

        $.ajax({
            type: "GET",
            url: "/ajax/admins/roles/permission",
            data: data,
            success: function(data) 
            {
                if(data.length > 0)
                {
                    mainData = JSON.parse(data);
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
        document.getElementById('name').value = mainData.results[0]["role_name"];

        for (var i = 0; i < mainData.results.length ; i++)
        {
            if(mainData.results[i]["is_deleted"] == 0)
            {
                // Check
                $("#"+mainData.results[i]["name"]).prop("checked", true);

                $("#"+mainData.results[i]["name"]).parent().parent().parent().parent().prev('input[type="checkbox"]').prop('checked', true);
                $("#"+mainData.results[i]["name"]).closest("li:has(li)").children("input[type='checkbox']").prop('checked', true);
            }
            else
            {
                // Uncheck
                $("#"+mainData.results[i]["name"]).prop("checked", false);
            }

            check_old.push(mainData.results[i]["name"] + '-' + mainData.results[i]["is_deleted"]);
            
        }
    }

    function submitMainForm()
    {   
        if($("#mainForm").attr("enabled") == 0)
        {
            return;
        }

        $("#mainForm").attr("enabled",0);

        var data  = {};
        var check = [];

        $.each($("input[type='checkbox']"), function(){
            var id = $(this).attr('id');
            var isChecked =  $("#"+id).is(':checked');
            if(isChecked == true)
            {
               $("#"+id).val(0);

            }
            else
            {
                $("#"+id).val(1);
            }

            if(!id.includes("parent"))
            {
                check.push(id + '-' + $("#"+id).val());
            }
        });

        var log_data = '{"name":"'+ $("#name").val() +'","check":"'+check_old+'"}';
        
        data["name"] = $("#name").val();
        data["id"] = utils.getParameterByName('id');
        data["check"] = check;
        data["log_old"] = log_data;

        utils.startLoadingBtn("btnSubmit","mainForm");

        $.ajax({
            url: "/ajax/admins/roles/update",
            type: "POST",
            data:  data,
            success: function(data)
            {
                // console.log(data);

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

        window.location.href = "/admins/roles";
    }

    function onMainModalDismissError()
    {
        $("#mainForm").attr("enabled",1);
    }

    $(function () {
        $(".checkall").click(function () {
            $(this).closest('div').find(':checkbox').prop('checked', this.checked);
        });
    });

    $(function () {
        $(".checkul").click(function () {
            $(this).next('ul').find(':checkbox').prop('checked', this.checked);
        });
    });

    $(function() {
        $(document).on("change", "li:has(li) > input[type='checkbox']", function() {
            $(this).parent().parent().prev('input[type="checkbox"]').prop('checked', this.checked);
            $(this).siblings('ul').find("input[type='checkbox']").prop('checked', this.checked);
        });

        $(document).on("change", "input[type='checkbox'] ~ ul input[type='checkbox']", function() {
            var l_1 = $(this).parent().parent().parent().find('.child').nextAll().find('input:checked').length;
            var l_2 = $(this).parent().parent().find('input:checked').length;
            var c = $(this).parent().find("input[type='checkbox']").is(':checked');

            if (l_2 == 0 && c == false) 
            {
                $(this).parent().parent().parent().parent().prev('input[type="checkbox"]').prop('checked', this.checked);
                $(this).closest("li:has(li)").children("input[type='checkbox']").prop('checked', c);
            } 
            else if (l_1 > 0 && c == true || l_2 > 0 && c == true) 
            {
                $(this).parent().parent().parent().parent().prev('input[type="checkbox"]').prop('checked', this.checked);
                $(this).closest("li:has(li)").children("input[type='checkbox']").prop('checked', c);
            }
        });
    })

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

    li
    {
        list-style-type: none;
    }

</style>

@endsection

@section('content')

<!-- Breadcrumb -->
<ol class="breadcrumb">
    <li class="breadcrumb-item">{{ __('app.admins.admin.breadcrumb.admins') }}</li>
    <li class="breadcrumb-item">
        <a href="/admins/roles">
            {{ __('app.admins.admin.breadcrumb.admins_role') }}
        </a>
    </li>
    <li class="breadcrumb-item active">{{ __('app.admins.admin.edit.breadcrumb.admins_role.edit') }}</li>
</ol>

<div class="container-fluid">
    <div class="animated fadeIn">

        <div class="card">

            <form method="POST" id="mainForm">
                @csrf

                <div class="card-header">
                    <strong>{{ __('app.admins.admins_role.create.header') }}</strong>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-4">

                            <div class="heading" style=" margin-bottom: 1rem">
                                {{ __('app.admins.admins_role.create.details') }} 
                            </div>

                            <div class="form-group row">
                                <label for="name" class="col-sm-12 col-md-2">{{ __('app.admins.admins_role.create.username') }}</label>
                                <input type="text" name="name" id="name" class="form-control col-sm-12 col-md-8" autocomplete="off" required="" disabled>
                            </div>
                        </div>
                    </div>

                    <fieldset class="form-group border" style="width:100%">
                        {{-- <legend class="w-auto px-2">{{ __('app.admins.admins_role.create.ca') }} </legend> --}}
                        <ul class="p-3">
                            <div class="row">
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <li>
                                            <input type="checkbox" class="checkall" name="settings" id="parent-s">{{ __('app.admins.admins_role.create.setting') }}
                                            <ul>
                                                <li>
                                                    <input type="checkbox" id="admin_create">{{ __('app.admins.admins_role.create.create_admin') }}
                                                </li>

                                                <li>
                                                    <input type="checkbox" id="admin_list">{{ __('app.admins.admins_role.create.admin_list') }}
                                                </li>

                                                <li>
                                                    <input type="checkbox" id="create_admin_roles">{{ __('app.admins.admins_role.create.create_admin_roles') }}
                                                </li>
                                                
                                                <li>
                                                    <input type="checkbox" id="admin_roles">{{ __('app.admins.admins_role.create.admin_roles') }}
                                                </li>
                                            </ul>
                                        </li>
                                    </div>
                                </div>
                            </div>
                        </ul>
                    </fieldset>

                </div>

                <div class="card-footer">

                    <button id="btnSubmit" class="btn btn-primary btn-ladda" data-style="expand-right">
                        <i class="fa fa-dot-circle-o"></i> {{ __('app.admins.admins_role.create.create') }}
                    </button>

                </div>

            </form>

        </div>

    </div>
</div>

@endsection
