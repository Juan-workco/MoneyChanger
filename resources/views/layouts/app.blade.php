<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- <link rel="shortcut icon" href="/coreui/img/favicon.png"> -->

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Money Changer') }}</title>

    <!-- Scripts -->
    <script src="/js/app.js"></script>
    <script src="/js/utils.js"></script>
    <script src="/js/auth.js"></script>
  
    <!-- JqueryUI -->
    <script src="/jqueryui/jquery-ui.min.js"></script>

    <!-- Custom CSS -->
    <link href="/css/custom.css" rel="stylesheet">

    <!-- JqueryUI -->
    <link href="/jqueryui/jquery-ui.min.css" rel="stylesheet">

    <!-- Multiple Select -->
    <link href="/select2/select2.min.css" rel="stylesheet" />
    <script src="/select2/select2.min.js"></script>

    <!-- CoreUI -->
    <link href="/coreui/vendors/css/flag-icon.min.css" rel="stylesheet">
    <link href="/coreui/vendors/css/font-awesome.min.css" rel="stylesheet">
    <link href="/coreui/vendors/css/simple-line-icons.min.css" rel="stylesheet">
    <link href="/coreui/vendors/css/spinkit.min.css" rel="stylesheet">
    <link href="/coreui/vendors/css/ladda-themeless.min.css" rel="stylesheet">
    <link href="/coreui/css/style.css" rel="stylesheet">

    <!-- CoreUI -->
    <script src="/coreui/vendors/js/pace.min.js"></script>
    <script src="/coreui/vendors/js/Chart.min.js" ></script>
    <script src="/coreui/vendors/js/spin.min.js"></script>
    <script src="/coreui/vendors/js/ladda.min.js"></script>
    <script src="/coreui/js/app.js" defer></script>

    <!--export Json to excel-->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.13.4/xlsx.core.min.js"></script>
    

    <script type="text/javascript">

    var locale = [];
    var audioElement  = "";
    var alertCount = 0;
    var resettlementAlertCount = 0;
    var manualAlertCount = 0;
    var appCurrency = "{{ Session::get('app_currency') }}";

    $(document).ready(function() 
    {
        auth.setUserType("{{Auth::user()->type}}");
        
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            error : function(xhr,textStatus,errorThrown) 
            {
                if(xhr.status == 440)
                    window.location.href = "/?k=1";
                else if(xhr.status == 441)
                    window.location.href = "/?k=2";
            }
        });

        var userTypeName = auth.getUserTypeName();

        if(auth.getUserType() == 'c' || auth.getUserType() == 'm')
        {
            $("#header_tier").html('(' + userTypeName + ')');

            $("#header_dd_tier").html('(' + userTypeName + ')');
        }

        prepareCommonLocale();
        timerTick();

        audioElement = document.createElement('audio');
        audioElement.setAttribute('muted', true);
        audioElement.setAttribute('src', '/audio/mpeg/definite.mp3');    
    });

    $(window).resize(function() 
    {
        timerTick();
    });    

    var days = locale;

    var timer = setInterval(timerTick, 1000);

    function timerTick() {
    var toGMT = 8;

    var now = new Date();
    var utc = new Date(now.getTime() + now.getTimezoneOffset() * 60000);
    var now = new Date(utc.getTime() + (toGMT * 60) * 60000);

    var currentHours = utils.padLeft(now.getHours(), 2, '0');
    var currentMinutes = utils.padLeft(now.getMinutes(), 2, '0');
    var currentSeconds = utils.padLeft(now.getSeconds(), 2, '0');

    var gmtSymbol = toGMT >= 0 ? '+' : '-';

    var str = now.getFullYear() 
        + '-' + utils.padLeft(now.getMonth() + 1, 2, '0')
        + '-' + utils.padLeft(now.getDate(), 2, '0') 
        + '&nbsp;' + currentHours 
        + ':' + currentMinutes 
        + ':' + currentSeconds 
        + '&nbsp;' + 'GMT ' + gmtSymbol + Math.abs(toGMT);

    $('#current_time').html(str);

    var $windowWidth = $(window).width();

    if ($windowWidth <= 751) {     
        $('#current_time').hide();
    } else {
        $('#current_time').show();
    }
}
        
    function prepareCommonLocale()
    {
         //localization
        //data table
        locale['utils.datatable.totalrecords'] = "{!! __('common.datatable.totalrecords') !!}";
        locale['utils.datatable.norecords'] = "{!! __('common.datatable.norecords') !!}";
        locale['utils.datatable.invaliddata'] = "{!! __('common.datatable.invaliddata') !!}";
        locale['utils.datatable.total'] = "{!! __('common.datatable.total') !!}";
        locale['utils.datatable.pagetotal'] = "{!! __('common.datatable.pagetotal') !!}";
        
        //modal
        locale['utils.modal.ok'] = "{!! __('common.modal.ok') !!}";
        locale['utils.modal.cancel'] = "{!! __('common.modal.cancel') !!}";

        locale['utils.datetime.day.0'] = "{!! __('app.header.sun') !!}";
        locale['utils.datetime.day.1'] = "{!! __('app.header.mon') !!}";
        locale['utils.datetime.day.2'] = "{!! __('app.header.tue') !!}";
        locale['utils.datetime.day.3'] = "{!! __('app.header.wed') !!}";
        locale['utils.datetime.day.4'] = "{!! __('app.header.thur') !!}";
        locale['utils.datetime.day.5'] = "{!! __('app.header.fri') !!}";
        locale['utils.datetime.day.6'] = "{!! __('app.header.sat') !!}";

        locale['info'] = "{!! __('common.modal.info') !!}";
        locale['success'] = "{!! __('common.modal.success') !!}";
        locale['error'] = "{!! __('common.modal.error') !!}";
    }

    function createWS()
    {
        window.Echo.options = 
            {
                broadcaster: 'pusher',
                key: "{{ env('PUSHER_APP_KEY') }}",
                cluster: "{{ env('PUSHER_APP_CLUSTER') }}",
                encrypted: true,
                wsHost: "{{ env('PUSHER_WSHOST') }}",
                wssPort: "{{ env('PUSHER_PORT') }}",
                disableStats: true,
            };

        window.Echo.connect();
    }
    </script>

    <style type="text/css">
        body
        {
            font-size: 12px;
        }
        .app-header.navbar .navbar-brand
        {
            background-image: none;
        }
        @media (max-width: 991.99px)
        {
            .app-header.navbar .navbar-brand
            {
                display:none;
            }
        }

        .sidebar .nav-dropdown .nav-dropdown-items .nav-item .nav-link
        {
            padding-left: 1.8rem;
        }

        table a
        {
            color: #0044CC !important;
        }
    </style>

    @yield('head')

</head>

<body class="app header-fixed sidebar-fixed aside-menu-fixed aside-menu-hidden 
    {{ Cookie::get('sidebar') }}">
    
    <header class="app-header navbar navbar-dark">

        <button class="navbar-toggler mobile-sidebar-toggler d-lg-none" type="button">
            <span class="navbar-toggler-icon"></span>
        </button>

        @can('system.accounts.all')
        <a class="navbar-brand" href="/home" style=""></a>
        @endcan

        <button class="navbar-toggler sidebar-toggler d-md-down-none" type="button">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div id="current_time" style="padding: 10px;"></div>

        <ul class="nav navbar-nav d-md-down-none">
            <li class="nav-item d-md-down-none">
                <div class="alert alert-danger" role="alert" style="margin: 5px;display:none" id="cron_alert_button">
                    <a href="/admins/admin/cronstatus" style="text decoration: none;color:black;">
                        <i class="icon-bell"></i><span class="badge badge-pill badge-danger" id="cron_alert_badge"></span>
                        {{ __('error.admin.cron_job_stopped') }}
                        </a>
                </div>
                <div class="alert alert-success" role="alert" style="margin: 5px;display:none" id="cron_active_button">
                    {{ __('error.admin.cron_job_is_running') }}
                </div>
            </li>
        </ul>
        <ul class="nav navbar-nav d-md-down-none">
            <li class="nav-item d-md-down-none">
                <div class="alert alert-danger" role="alert" style="margin: 5px;display:none" id="resettlement_alert_button">
                    <a href="/sb/resettlement/list" style="text decoration: none;color:black;">
                        <i class="icon-bell"></i><span class="badge badge-pill badge-danger" id="resettlement_alert_badge"></span>
                        Resettled Bets
                    </a>
                </div>
                <div class="alert alert-success" role="alert" style="margin: 5px;display:none" id="resettlement_active_button">
                    No Resettled Bets
                </div>
            </li>
        </ul>

        <ul class="nav navbar-nav d-md-down-none">
            <li class="nav-item d-md-down-none">
                <div class="alert alert-danger" role="alert" style="margin: 5px;display:none" id="manual_alert_button">
                    <a href="/sb/bet/manualsettlement" style="text decoration: none;color:black;">
                        <i class="icon-bell"></i><span class="badge badge-pill badge-danger" id="manual_alert_badge"></span>
                    Pending Bets
                    </a>
                </div>
                <div class="alert alert-success" role="alert" style="margin: 5px;display:none" id="manual_active_button">
                    No Bets
                </div>
            </li>
        </ul>

        <ul class="nav navbar-nav ml-auto">
            <li class="nav-item px-1">
                <span><b id="tier"></b></span>
            </li>
        
            <li class="nav-item px-1">
                <span class="d-none d-md-block w-100">{{ Auth::user()->username }} <b id="header_tier"></b></span>
            </li>

            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                    
                    <img src="/coreui/img/avatars/0.jpg" class="img-avatar" alt="">
                </a>
                
                <div class="dropdown-menu dropdown-menu-right">
                    <div class="dropdown-header text-center">
                        <strong>{{ Auth::user()->username }} <b id="header_dd_tier"></b></strong>
                    </div>
                    
                    <a class="dropdown-item" href="{{ route('changepassword') }}">
                        <i class="fa fa-lock"></i> {{ __('app.header.changepassword') }}
                    </a>

                    <a class="dropdown-item" href="{{ route('logout') }}" 
                        onclick="event.preventDefault();
                                     document.getElementById('logout-form').submit();">
                        <i class="fa fa-lock"></i> {{ __('app.header.logout') }}
                    </a>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>

                </div>
            </li>

            {{-- <li class="nav-item dropdown">
                <a class="nav-link nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                    <i class="flag-icon flag-icon-{{ Helper::getLocaleFlag() }} h1" title="{{ __('app.header.language') }}" id="gb" style="width:35px"></i> 
                </a>
                
                <div class="dropdown-menu dropdown-menu-right">

                    <div class="dropdown-header text-center">
                        <strong>{{ __('app.header.language') }}</strong>
                    </div>

                     <a class="dropdown-item" href="#"
                        onclick="event.preventDefault();
                                    document.getElementById('locale').value = 'en';
                                    document.getElementById('form-locale').submit();">
                        </i>{{ __('app.login.admin.english') }}
                    </a>

                    <form id="form-locale" action="{{ route('locale') }}" method="POST" style="display: none;">
                        @csrf
                        <input type="hidden" id="locale" name="locale" value="">
                    </form>

                </div>

            </li>    --}}

        </ul>
    </header>

    <div class="app-body">
        <div class="sidebar">
            <nav class="sidebar-nav">
                <ul class="nav">
                    @can('system.accounts.all')
                    <li class="nav-item">
                        <a class="nav-link" href="/home"><i class="icon-home"></i> {{ __('app.sidebar.home') }}</a>
                    </li>
                    @endcan

                    @can('system.accounts.all')
                    <li class="nav-item">
                        <a class="nav-link" href="/remittance">
                            <i class="icon-notebook">
                            </i> {{ __('app.sidebar.remittance') }}
                        </a>
                    </li>
                    @endcan

                    @can('system.accounts.admin')
                    <li class="nav-item nav-dropdown">
                        <a class="nav-link nav-dropdown-toggle" href="#">
                            <i class="fa fa-users"></i> 
                            {{ __('app.sidebar.customer') }}
                        </a>
                        
                        <ul class="nav-dropdown-items">
                            <li class="nav-item">
                                <a class="nav-link" href="/customer/list">
                                    <i class="icon-list"></i> 
                                    {{ __('app.sidebar.customer.list') }}
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endcan

                    @can('system.accounts.admin')
                    <li class="nav-item nav-dropdown">
                        <a class="nav-link nav-dropdown-toggle" href="#">
                            <i class="fa fa-money"></i> 
                            {{ __('app.sidebar.currency') }}
                        </a>
                        
                        <ul class="nav-dropdown-items">
                            @can('permissions.create_admin_roles')
                            <li class="nav-item">
                                <a class="nav-link" href="/currency/new">
                                    <i class="fa fa-plus"></i>  
                                    {{ __('app.sidebar.currency.new') }}
                                </a>
                            </li>
                            @endcan

                            @can('permissions.admin_roles')
                            <li class="nav-item">
                                <a class="nav-link" href="/currency/list">
                                    <i class="icon-list"></i> 
                                    {{ __('app.sidebar.currency.list') }}
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endcan

                    @can('system.accounts.admin')
                    <li class="nav-item nav-dropdown">
                        <a class="nav-link nav-dropdown-toggle" href="#">
                            <i class="fa fa-university"></i> 
                            {{ __('app.sidebar.payment') }}
                        </a>
                        
                        <ul class="nav-dropdown-items">
                            <li class="nav-item">
                                <a class="nav-link" href="/payment/method">
                                    <i class="fa fa-credit-card"></i> 
                                    {{ __('app.sidebar.payment.method') }}
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="/payment/account">
                                    <i class="fa fa-address-book-o"></i> 
                                    {{ __('app.sidebar.payment.account') }}
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endcan

                    @can('system.accounts.admin')
                    @canany(['permissions.create_admin','permissions.view_admin_list','permissions.create_admin_roles','permissions.admin_roles']) 
                    <li class="nav-item nav-dropdown">
                        <a class="nav-link nav-dropdown-toggle" href="#">
                            <i class="fa fa-user-o"></i> 
                            {{ __('app.sidebar.admins') }}
                        </a>
                        
                        <ul class="nav-dropdown-items">
                            @can('permissions.admin_roles')
                            <li class="nav-item">
                                <a class="nav-link" href="/admins/roles"><i class="icon-list"></i> 
                                    {{ __('app.sidebar.settings.admin.roles.list') }}
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endcan
                    @endcan

                    @can('system.accounts.admin')
                        <li class="nav-item">
                            <a class="nav-link" href="/setting/system">
                                <i class="icon-settings"></i>
                                {{ __('app.sidebar.settings.system') }}
                            </a>
                        </li>
                    @endcan
                </ul>
            </nav>

            <button class="sidebar-minimizer brand-minimizer" type="button"></button>
        </div>

        <!-- Main content -->
        <main class="main">
            @yield('content')
        </main>

    </div>

    <footer class="app-footer">
        <span>© {{ date('Y') }} {{ __('app.footer.money_changer') }}</span>
    </footer>

</body>
</html>