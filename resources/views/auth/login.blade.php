<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="/js/utils.js"></script>
    
    <!-- CoreUI -->
    <link href="/coreui/vendors/css/font-awesome.min.css" rel="stylesheet">
    <link href="/coreui/vendors/css/simple-line-icons.min.css" rel="stylesheet">
    <link href="/coreui/css/style.css" rel="stylesheet">
    <link href="/coreui/css/login_v1_1.css" rel="stylesheet">

    <script type="text/javascript">
        
        $(document).ready(function() 
        {
            if(utils.getParameterByName("k") == 1)
            {
                alert("{!! __('error.login.multiple_login') !!}");
                window.history.pushState({}, document.title, "/");
            }
            else if(utils.getParameterByName("k") == 2)
            {
                alert("{!! __('error.login.account_inactive') !!}");
                window.history.pushState({}, document.title, "/");
            }

            var locale = "{{ App::getLocale() }}";

            $('#language_text').html($("#locale_" + locale ).html());

            $('.dropdown-menu').css('width', document.getElementById("language").offsetWidth);

        });

    </script>

    <style>  

        body
        {
            background: rgba(0,0,0,1);
            background-repeat: no-repeat;
            background-size: cover;
            -webkit-background-size: cover;
            -moz-background-size: cover;
            -o-background-size: cover;
            overflow: hidden;
        }

        #username,#password,#language
        {
            text-align: center;
            column-width: inherit;
            color: #fff;
            background: transparent;
            border: solid;
            border-color: #d4b437;
            border-width: 2px;
            border-radius: 20px;
        }

        #username
        {
            color: #fef2b5;
            background-size:10px;
            background-repeat: no-repeat;
            background-position: 5% 50%;
        }

        #password
        {
            color: #fef2b5;
            background-size:10px;
            background-repeat: no-repeat;
            background-position: 5% 50%;
        }

        #language
        {
            position: relative;
            display: flex;
            justify-content: center;
        }

        .dropdown-item
        {
            background: #191919;
            color: #8e8a79;
            font-size: 12px;
            padding: 10px 14px;
            border-radius: 8px;
            border: none !important;
        }

        .dropdown-menu
        {
            border-radius: 8px;
            width: 100%; 
            transform: translate3d(15px, 45px, 0px) !important;
            background: none;
            border: 1px solid #dcc45a !important;
            background: #191919;
            max-height: 150px;
            overflow: auto;
        }

        .dropdown-item:hover
        {
            background: #c5991c !important;
        }

        .button-login:focus
        {
            box-shadow: 0 0 0 0.2rem #d4b437 !important;
        }

        ::-webkit-scrollbar 
        {
            width: 15px;
        }

        /* Track */
        ::-webkit-scrollbar-track 
        {
            background: #191919;
            border-radius: 10px;
        }
         
        /* Handle */
        ::-webkit-scrollbar-thumb 
        {
            background: #dcc45a; 
            border: 5px solid #191919;
            border-radius: 10px;
        }

        /* Handle on hover */
        ::-webkit-scrollbar-thumb:hover 
        {
            background: #555; 
        }

    </style>

</head> 


<body class="app flex-row align-items-center">
    <div class="container" style="height: 100% !important;">

        <div class="row d-flex justify-content-center">
            <div class="col-12 col-md-6 text-center" style="align-self:center;">
                <h5 style="color: white; font-size: 13px; padding: 1rem 0;">{{ __('app.login.login') }}</h5>

                <form method="POST" action="{{ route('login') }}" aria-label="{{ __('Login') }}">
                @csrf
                    <div class="form-group row justify-content-center">

                        <!-- <label for="username" class="col-sm-3 col-form-label"></label>  -->

                        <div class="col-12 col-md-8">
                            <input id="username" type="text" class="form-control{{ $errors->has('username') ? ' is-invalid' : '' }}" name="username" value="{{ old('username') }}" autocomplete="off" placeholder="{{ __('app.login.username') }}" required autofocus oninvalid="this.setCustomValidity('{!! __('error.input.required') !!}')" oninput="setCustomValidity('')">

                            @if ($errors->has('username'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('username') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="form-group row justify-content-center">
                        <!-- <label for="password" class="d-none d-md-block col-sm-3 col-form-label"></label>  -->

                        <div class="col-12 col-md-8">
                            <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" placeholder="{{ __('app.login.password') }}" required oninvalid="this.setCustomValidity('{!! __('error.input.required') !!}')" oninput="setCustomValidity('')">

                            @if ($errors->has('password'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                    {{-- <div class="form-group row justify-content-center">
                        <!-- <label for="language" class="d-none d-md-block col-sm-3 col-form-label"></label>  -->

                        <div class="col-12 col-md-8">
                            <a class="nav-link nav-link form-control" id="language" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                                <span id="language_text" style="color:#8e8a79;">{{ __('app.login.admin.language') }}</span>
                                <i class="fa fa-angle-down" style="align-self: center; position: absolute; right: 10px; color: #8e8a79;"></i>
                            </a>

                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="#"
                                    onclick="event.preventDefault();
                                                document.getElementById('locale').value = 'en';
                                                document.getElementById('form-locale').submit();">
                                                <span class="language_selection_text" id="locale_en">{{ __('app.login.admin.english') }}</span>
                                </a>
                            </div>
                        </div>
                    </div> --}}
                    <div class="form-group row justify-content-center">

                        <!-- <label for="loginbutton" class="col-sm-3 col-form-label"></label>  -->
                        <div class="col-12 col-md-8">
                            <button type="submit" class="button-background button-login px-4" style=outline:none;>{{ __('app.login.login') }}</button>
                        </div>
                    </div>

                </form>


            </div>

            <form id="form-locale" action="{{ route('locale') }}" method="POST" style="display: none;">
                @csrf
                <input type="hidden" id="locale" name="locale" value="">
            </form>

        </div>
    </div>
</body>
</html>