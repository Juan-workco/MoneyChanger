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
    var toGMT;

    if (appCurrency === 'USD') {
        toGMT = -4; 
    } else if (appCurrency === 'KRW') {
        toGMT = +9; 
    } else {
        toGMT = 0; 
    }

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
    }

    function prepareSbLocale()
    {
        //sb bet details
        locale['mainData.number'] = "NO";
        locale['mainData.date'] = "Date";
        locale['mainData.bet_type'] = "BetType";
        locale['mainData.game'] = "Game";
        locale['mainData.competition'] = "Competition";
        locale['mainData.result'] = "Result";
        locale['mainData.username'] = "Username";
        locale['mainData.bet_time'] = "Bet Time";
        locale['mainData.bet_no'] = "Bet No";
        locale['mainData.odds'] = "Odds";
        locale['mainData.stake'] = "Stake";
        locale['mainData.max_win'] = "Payout";
        locale['mainData.home_team'] = "Home Team";
        locale['mainData.away_team'] = "Away Team";
        locale['mainData.win_lose'] = "W/L";
        locale['mainData.status'] = "Status";

        locale['event.bettype.match.Ordinary Time'] = "{!! __('app.events.main.bet.match.Ordinary Time') !!}";
        locale['event.bettype.match.Whole Match'] = "{!! __('app.events.main.bet.match.Whole Match') !!}";
        locale['event.bettype.match.1st Half (Ordinary Time)'] = "{!! __('app.events.main.bet.match.1st Half (Ordinary Time)') !!}";
        locale['event.bettype.match.2nd Half (Ordinary Time)'] = "{!! __('app.events.main.bet.match.2nd Half (Ordinary Time)') !!}";
        locale['event.bettype.match.Overtime Excluding Penalty Round'] = "{!! __('app.events.main.bet.match.Overtime Excluding Penalty Round') !!}";

        locale['event.bettype.match.hometeam'] = "{!! __('app.events.main.bet.match.hometeam') !!}";
        locale['event.bettype.match.awayteam'] = "{!! __('app.events.main.bet.match.awayteam') !!}";

        locale['event.bettype.match.1st Set'] = "{!! __('app.events.main.bet.match.1st Set') !!}";
        locale['event.bettype.match.2nd Set'] = "{!! __('app.events.main.bet.match.2nd Set') !!}";
        locale['event.bettype.match.3rd Set'] = "{!! __('app.events.main.bet.match.3rd Set') !!}";
        locale['event.bettype.match.4th Set'] = "{!! __('app.events.main.bet.match.4th Set') !!}";
        locale['event.bettype.match.5th Set'] = "{!! __('app.events.main.bet.match.5th Set') !!}";

        locale['event.bettype.match.1st Inning'] = "{!! __('app.events.main.bet.match.1st Inning') !!}";
        locale['event.bettype.match.2nd Inning'] = "{!! __('app.events.main.bet.match.2nd Inning') !!}";
        locale['event.bettype.match.3rd Inning'] = "{!! __('app.events.main.bet.match.3rd Inning') !!}";
        locale['event.bettype.match.4th Inning'] = "{!! __('app.events.main.bet.match.4th Inning') !!}";
        locale['event.bettype.match.5th Inning'] = "{!! __('app.events.main.bet.match.5th Inning') !!}";
        locale['event.bettype.match.6th Inning'] = "{!! __('app.events.main.bet.match.6th Inning') !!}";
        locale['event.bettype.match.7th Inning'] = "{!! __('app.events.main.bet.match.7th Inning') !!}";
        locale['event.bettype.match.8th Inning'] = "{!! __('app.events.main.bet.match.8th Inning') !!}";
        locale['event.bettype.match.9th Inning'] = "{!! __('app.events.main.bet.match.9th Inning') !!}";

        locale['event.bettype.match.First Three Innings'] = "{!! __('app.events.main.bet.match.First Three Innings') !!}";
        locale['event.bettype.match.First Five Innings'] = "{!! __('app.events.main.bet.match.First Five Innings') !!}";
        locale['event.bettype.match.First Seven Innings'] = "{!! __('app.events.main.bet.match.First Seven Innings') !!}";

        locale['event.bettype.match.1st Quarter'] = "{!! __('app.events.main.bet.match.1st Quarter') !!}";
        locale['event.bettype.match.2nd Quarter'] = "{!! __('app.events.main.bet.match.2nd Quarter') !!}";
        locale['event.bettype.match.3rd Quarter'] = "{!! __('app.events.main.bet.match.3rd Quarter') !!}";
        locale['event.bettype.match.4th Quarter'] = "{!! __('app.events.main.bet.match.4th Quarter') !!}";

        locale['event.bettype.match.1st Period'] = "{!! __('app.events.main.bet.match.1st Period') !!}";
        locale['event.bettype.match.2nd Period'] = "{!! __('app.events.main.bet.match.2nd Period') !!}";
        locale['event.bettype.match.3rd Period'] = "{!! __('app.events.main.bet.match.3rd Period') !!}";

        locale['event.bettype.match.Map 1'] = "{!! __('app.events.main.bet.match.Map 1') !!}";
        locale['event.bettype.match.Map 2'] = "{!! __('app.events.main.bet.match.Map 2') !!}";
        locale['event.bettype.match.Map 3'] = "{!! __('app.events.main.bet.match.Map 3') !!}";
        locale['event.bettype.match.Map 4'] = "{!! __('app.events.main.bet.match.Map 4') !!}";
        locale['event.bettype.match.Map 5'] = "{!! __('app.events.main.bet.match.Map 5') !!}";
        locale['event.bettype.match.Ordinary Time (Map 1)'] = "{!! __('app.events.main.bet.match.Map 1') !!}";
        locale['event.bettype.match.Ordinary Time (Map 2)'] = "{!! __('app.events.main.bet.match.Map 2') !!}";
        locale['event.bettype.match.Ordinary Time (Map 3)'] = "{!! __('app.events.main.bet.match.Map 3') !!}";
        locale['event.bettype.match.Ordinary Time (Map 4)'] = "{!! __('app.events.main.bet.match.Map 4') !!}";
        locale['event.bettype.match.Ordinary Time (Map 5)'] = "{!! __('app.events.main.bet.match.Map 5') !!}";

        locale['maindata.bettype.Over'] = "{!! __('app.sb.bettype.over') !!}";
        locale['maindata.bettype.Under'] = "{!! __('app.sb.bettype.under') !!}";

        //display period
        locale['event.match.minute'] = "{!! __('app.main.event.match.minute') !!}";
        locale['event.match.minute2'] = "{!! __('app.main.event.match.minute2') !!}";

        locale['event.match.1st Half (Ordinary Time)'] = "{!! __('app.main.event.match.1st Half (Ordinary Time)') !!}";
        locale['event.match.2nd Half (Ordinary Time)'] = "{!! __('app.main.event.match.2nd Half (Ordinary Time)') !!}";
        locale['event.match.1st Half (Overtime)'] = "{!! __('app.main.event.match.1st Half (Overtime)') !!}";
        locale['event.match.2nd Half (Overtime)'] = "{!! __('app.main.event.match.2nd Half (Overtime)') !!}";
        locale['event.match.Halftime'] = "{!! __('app.main.event.match.halftime') !!}";

        locale['event.match.1st Quarter'] = "{!! __('app.main.event.match.1st_quarter') !!}";
        locale['event.match.2nd Quarter'] = "{!! __('app.main.event.match.2nd_quarter') !!}";
        locale['event.match.3rd Quarter'] = "{!! __('app.main.event.match.3rd_quarter') !!}";
        locale['event.match.4th Quarter'] = "{!! __('app.main.event.match.4th_quarter') !!}";
        locale['event.match.Overtime'] = "{!! __('app.main.event.match.overtime') !!}";
        locale['event.match.Overtime Excluding Penalty Round'] = "{!! __('app.main.event.match.overtime') !!}";
        locale['event.match.1st Intermission'] = "{!! __('app.main.event.match.1st_intermission') !!}";
        locale['event.match.2nd Intermission'] = "{!! __('app.main.event.match.2nd_intermission') !!}";
        locale['event.match.3rd Intermission'] = "{!! __('app.main.event.match.3rd_intermission') !!}";
        locale['event.match.4th Intermission'] = "{!! __('app.main.event.match.4th_intermission') !!}";
        locale['event.match.current_part.live'] = "{!! __('app.main.event.match.current_part.live') !!}";
        
        locale['event.match.baseball.1st Intermission'] = "{!! __('app.main.event.match.baseball.1st_intermission') !!}";
        locale['event.match.baseball.2nd Intermission'] = "{!! __('app.main.event.match.baseball.2nd_intermission') !!}";
        locale['event.match.baseball.3rd Intermission'] = "{!! __('app.main.event.match.baseball.3rd_intermission') !!}";
        locale['event.match.baseball.4th Intermission'] = "{!! __('app.main.event.match.baseball.4th_intermission') !!}";
        locale['event.match.baseball.5th Intermission'] = "{!! __('app.main.event.match.baseball.5th_intermission') !!}";
        locale['event.match.baseball.6th Intermission'] = "{!! __('app.main.event.match.baseball.6th_intermission') !!}";
        locale['event.match.baseball.7th Intermission'] = "{!! __('app.main.event.match.baseball.7th_intermission') !!}";
        locale['event.match.baseball.8th Intermission'] = "{!! __('app.main.event.match.baseball.8th_intermission') !!}";

        locale['event.match.1st Inning'] = "{!! __('app.main.event.match.1st Inning') !!}";
        locale['event.match.2nd Inning'] = "{!! __('app.main.event.match.2nd Inning') !!}";
        locale['event.match.3rd Inning'] = "{!! __('app.main.event.match.3rd Inning') !!}";
        locale['event.match.4th Inning'] = "{!! __('app.main.event.match.4th Inning') !!}";
        locale['event.match.5th Inning'] = "{!! __('app.main.event.match.5th Inning') !!}";
        locale['event.match.6th Inning'] = "{!! __('app.main.event.match.6th Inning') !!}";
        locale['event.match.7th Inning'] = "{!! __('app.main.event.match.7th Inning') !!}";
        locale['event.match.8th Inning'] = "{!! __('app.main.event.match.8th Inning') !!}";
        locale['event.match.9th Inning'] = "{!! __('app.main.event.match.9th Inning') !!}";
        locale['event.match.First Three Innings'] = "{!! __('app.main.event.match.First Three Innings') !!}";
        locale['event.match.First Five Innings'] = "{!! __('app.main.event.match.First Five Innings') !!}";
        locale['event.match.First Seven Innings'] = "{!! __('app.main.event.match.First Seven Innings') !!}";

        locale['event.match.Top 1'] = "{!! __('app.main.event.match.Top 1') !!}";
        locale['event.match.Top 2'] = "{!! __('app.main.event.match.Top 2') !!}";
        locale['event.match.Top 3'] = "{!! __('app.main.event.match.Top 3') !!}";
        locale['event.match.Top 4'] = "{!! __('app.main.event.match.Top 4') !!}";
        locale['event.match.Top 5'] = "{!! __('app.main.event.match.Top 5') !!}";
        locale['event.match.Top 6'] = "{!! __('app.main.event.match.Top 6') !!}";
        locale['event.match.Top 7'] = "{!! __('app.main.event.match.Top 7') !!}";
        locale['event.match.Top 8'] = "{!! __('app.main.event.match.Top 8') !!}";
        locale['event.match.Top 9'] = "{!! __('app.main.event.match.Top 9') !!}";

        locale['event.match.break'] = "{!! __('app.main.event.match.break') !!}";
        locale['event.match.Break 1'] = "{!! __('app.main.event.match.break') !!}";
        locale['event.match.Break 2'] = "{!! __('app.main.event.match.break') !!}";
        locale['event.match.Break 3'] = "{!! __('app.main.event.match.break') !!}";
        locale['event.match.Break 4'] = "{!! __('app.main.event.match.break') !!}";
        locale['event.match.Break 5'] = "{!! __('app.main.event.match.break') !!}";
        locale['event.match.Break 6'] = "{!! __('app.main.event.match.break') !!}";
        locale['event.match.Break 7'] = "{!! __('app.main.event.match.break') !!}";
        locale['event.match.Break 8'] = "{!! __('app.main.event.match.break') !!}";
        locale['event.match.Break 9'] = "{!! __('app.main.event.match.break') !!}";

        locale['event.match.Bottom 1'] = "{!! __('app.main.event.match.Bottom 1') !!}";
        locale['event.match.Bottom 2'] = "{!! __('app.main.event.match.Bottom 2') !!}";
        locale['event.match.Bottom 3'] = "{!! __('app.main.event.match.Bottom 3') !!}";
        locale['event.match.Bottom 4'] = "{!! __('app.main.event.match.Bottom 4') !!}";
        locale['event.match.Bottom 5'] = "{!! __('app.main.event.match.Bottom 5') !!}";
        locale['event.match.Bottom 6'] = "{!! __('app.main.event.match.Bottom 6') !!}";
        locale['event.match.Bottom 7'] = "{!! __('app.main.event.match.Bottom 7') !!}";
        locale['event.match.Bottom 8'] = "{!! __('app.main.event.match.Bottom 8') !!}";
        locale['event.match.Bottom 9'] = "{!! __('app.main.event.match.Bottom 9') !!}";

        locale['event.match.1st Period'] = "{!! __('app.main.event.match.1st Period') !!}";
        locale['event.match.2nd Period'] = "{!! __('app.main.event.match.2nd Period') !!}";
        locale['event.match.3rd Period'] = "{!! __('app.main.event.match.3rd Period') !!}";
        locale['event.match.timeout'] = "{!! __('app.main.event.match.timeout') !!}";

        locale['event.match.1st'] = "{!! __('app.main.event.match.1stset') !!}";
        locale['event.match.2nd'] = "{!! __('app.main.event.match.2ndset') !!}";
        locale['event.match.3rd'] = "{!! __('app.main.event.match.3rdset') !!}";
        locale['event.match.4th'] = "{!! __('app.main.event.match.4thset') !!}";
        locale['event.match.5th'] = "{!! __('app.main.event.match.5thset') !!}";

        locale['event.match.Map 1'] = "{!! __('app.events.main.bet.match.Map 1') !!}";
        locale['event.match.Map 2'] = "{!! __('app.events.main.bet.match.Map 2') !!}";
        locale['event.match.Map 3'] = "{!! __('app.events.main.bet.match.Map 3') !!}";
        locale['event.match.Map 4'] = "{!! __('app.events.main.bet.match.Map 4') !!}";
        locale['event.match.Map 5'] = "{!! __('app.events.main.bet.match.Map 5') !!}";
        locale['event.match.Ordinary Time (Map 1)'] = "{!! __('app.events.main.bet.match.Map 1') !!}";
        locale['event.match.Ordinary Time (Map 2)'] = "{!! __('app.events.main.bet.match.Map 2') !!}";
        locale['event.match.Ordinary Time (Map 3)'] = "{!! __('app.events.main.bet.match.Map 3') !!}";
        locale['event.match.Ordinary Time (Map 4)'] = "{!! __('app.events.main.bet.match.Map 4') !!}";
        locale['event.match.Ordinary Time (Map 5)'] = "{!! __('app.events.main.bet.match.Map 5') !!}";

        //modal
        locale['info'] = "{!! __('common.modal.info') !!}";
        locale['success'] = "{!! __('common.modal.success') !!}";
        locale['error'] = "{!! __('common.modal.error') !!}";

        locale['reason.1'] = "{!! __('option.reason.1') !!}";
        locale['reason.3'] = "{!! __('option.reason.3') !!}";
        locale['reason.4'] = "{!! __('option.reason.4') !!}";
        locale['reason.7'] = "{!! __('option.reason.7') !!}";
        locale['reason.11'] = "{!! __('option.reason.11') !!}";
        locale['reason.13'] = "{!! __('option.reason.13') !!}";
        locale['reason.15'] = "{!! __('option.reason.15') !!}";
        locale['reason.16'] = "{!! __('option.reason.16') !!}";
        locale['reason.18'] = "{!! __('option.reason.18') !!}";
        locale['reason.21'] = "{!! __('option.reason.21') !!}";
        locale['reason.22'] = "{!! __('option.reason.22') !!}";
        locale['reason.23'] = "{!! __('option.reason.23') !!}";
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

        #modal-sb-details .modal-lg
        {
            max-width: 1250px;
        }

        #modal-sb-details .bg-info
        {
            color:black !important;
        }

        #modal-sb-details a
        {
            color: #1877f2;
        }

        #modal-sb-details table 
        {
            width: 100%;
        }

        #modal-sb-details .modal-body 
        {
            overflow-y: auto;
            max-height: 550px;
        }

        #modal-sb-details td .span
        {
            white-space: nowrap;
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

            <li class="nav-item dropdown">
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

            </li>   

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

                    {{-- @can('system.accounts.admin') --}}
                    {{-- @canany(['permissions.create_admin','permissions.view_admin_list','permissions.view_maintenance_schedule','permissions.admin_log', 'permissions.cron_status'])  --}}
                    <li class="nav-item nav-dropdown">
                        <a class="nav-link nav-dropdown-toggle" href="#"><i class="icon-settings"></i> 
                            {{ __('app.sidebar.settings') }}
                        </a>
                        
                        <ul class="nav-dropdown-items">
                            @can('system.accounts.super.admin')
                            <li class="nav-item">
                                <a class="nav-link" href="/admins/roles/new"><i class="icon-user-follow"></i>  
                                    {{ __('app.sidebar.settings.roles.create') }}
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="/admins/roles"><i class="icon-list"></i> 
                                    {{ __('app.sidebar.settings.roles.list') }}
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    {{-- @endcan --}}
                    {{-- @endcan --}}
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