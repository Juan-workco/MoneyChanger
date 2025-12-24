<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Money Changer Admin')</title>

    <!-- CoreUI CSS -->
    <link href="{{ asset('coreui/css/style.css') }}" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>

    <style>
        /* Custom overrides for CoreUI v1 */
        .app-body {
            margin-top: 55px;
            /* Height of header */
        }

        .sidebar {
            /* margin-left: 0 !important; */
        }

        .sidebar .nav-link i {
            margin-right: 10px;
        }

        /* Mobile responsive fixes */
        @media (max-width: 991px) {
            .main {
                padding: 15px !important;
            }

            .main .container-fluid {
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            /* Ensure sidebar works on mobile */
            body.sidebar-show {
                overflow: hidden;
            }
        }

        /* Ensure main content takes full width */
        .main .container-fluid {
            max-width: 100%;
        }
    </style>

    @yield('styles')
</head>

<body class="app header-fixed sidebar-fixed aside-menu-fixed aside-menu-hidden pace-done">
    <!-- Header -->
    <header class="app-header navbar">
        <button class="navbar-toggler mobile-sidebar-toggler d-lg-none" type="button">
            <span class="navbar-toggler-icon"></span>
        </button>

        <button class="navbar-toggler sidebar-toggler d-md-down-none" type="button">
            <span class="navbar-toggler-icon"></span>
        </button>

        <ul class="nav navbar-nav ml-auto pr-2">
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true"
                    aria-expanded="false">
                    <i class="fas fa-user-circle fa-lg"></i>
                    <span class="d-md-inline-block">{{ Auth::user()->name }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <div class="dropdown-header text-center">
                        <strong>Account</strong>
                    </div>
                    <a class="dropdown-item" href="{{ route('password.reset') }}">
                        <i class="fas fa-key"></i> Change Password
                    </a>
                    <a class="dropdown-item" href="#"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </li>
        </ul>
    </header>

    <div class="app-body">
        <!-- Sidebar -->
        <div class="sidebar">
            <nav class="sidebar-nav">
                <ul class="nav">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('dashboard') || request()->is('/') ? 'active' : '' }}"
                            href="{{ route('dashboard') }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>

                    <li class="nav-title">Management</li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('customers*') ? 'active' : '' }}"
                            href="{{ route('customers.index') }}">
                            <i class="nav-icon fas fa-users"></i> Customers
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('transactions*') ? 'active' : '' }}"
                            href="{{ route('transactions.index') }}">
                            <i class="nav-icon fas fa-receipt"></i> Transactions
                        </a>
                    </li>

                    <li class="nav-title">Reports</li>
                    @if(Auth::user()->hasPermission('view_reports'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('reports/daily*') ? 'active' : '' }}"
                                href="{{ route('reports.daily') }}">
                                <i class="nav-icon fas fa-calendar-day"></i> Daily Report
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('reports/balance-sheet*') ? 'active' : '' }}"
                                href="{{ route('reports.balance-sheet') }}">
                                <i class="nav-icon fas fa-balance-scale"></i> Balance Sheet
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('reports/profit-loss*') ? 'active' : '' }}"
                                href="{{ route('reports.profit-loss') }}">
                                <i class="nav-icon fas fa-chart-line"></i> Profit & Loss
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('reports/commission*') ? 'active' : '' }}"
                                href="{{ route('reports.commission') }}">
                                <i class="nav-icon fas fa-percentage"></i> Commission Report
                            </a>
                        </li>
                    @endif

                    @if(Auth::user()->hasPermission('view_currencies') || Auth::user()->hasPermission('view_exchange_rates') || Auth::user()->hasPermission('manage_users') || Auth::user()->hasPermission('manage_roles'))
                    <li class="nav-title">Administration</li>
                    @endif

                    @if(Auth::user()->hasPermission('view_currencies'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('currencies*') ? 'active' : '' }}"
                            href="{{ route('currencies.index') }}">
                            <i class="nav-icon fas fa-coins"></i> Currencies
                        </a>
                    </li>
                    @endif

                    @if(Auth::user()->hasPermission('view_exchange_rates'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('exchange-rates*') ? 'active' : '' }}"
                            href="{{ route('exchange-rates.index') }}">
                            <i class="nav-icon fas fa-chart-line"></i> Exchange Rates
                        </a>
                    </li>
                    @endif

                    @if(Auth::user()->hasPermission('manage_users'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('users*') ? 'active' : '' }}"
                                href="{{ route('users.index') }}">
                                <i class="nav-icon fas fa-users-cog"></i> Users
                            </a>
                        </li>
                    @endif

                    @if(Auth::user()->hasPermission('manage_roles'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('roles*') ? 'active' : '' }}"
                                href="{{ route('roles.index') }}">
                                <i class="nav-icon fas fa-user-shield"></i> Roles & Permissions
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('settings*') ? 'active' : '' }}"
                                href="{{ route('settings.index') }}">
                                <i class="nav-icon fas fa-cog"></i> Settings
                            </a>
                        </li>
                    @endif
                </ul>
            </nav>
            <button class="sidebar-minimizer brand-minimizer" type="button"></button>
        </div>

        <!-- Main Content -->
        <main class="main">
            <div class="container-fluid">
                <div class="animated fadeIn">
                    <!-- Flash Messages -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                            <i class="fas fa-exclamation-triangle ml-3"></i>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </div>
        </main>
    </div>

    <footer class="app-footer">
        <div>
            <span>&copy; {{ date('Y') }} Money Changer</span>
        </div>
    </footer>

    <!-- CoreUI and necessary plugins -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Flatpickr -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="{{ asset('coreui/js/app.js') }}"></script>

    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function () {
            $('.alert').fadeOut('slow');
        }, 5000);

        // CSRF token for AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

    @yield('scripts')
</body>

</html>