@extends('layouts.app')

@section('content')

<!-- Breadcrumb -->
<ol class="breadcrumb">
	<li class="breadcrumb-item">{{ __('app.home.home') }}</li>
</ol>

<div class="container-fluid">
	<div class="animated fadeIn">
		<div class="row">
            <div class="col-sm-6 col-md-3">
              <div class="card text-white bg-info">
                <div class="card-body">
                  <div class="h1 text-muted text-right mb-4">
                    <i class="icon-people"></i>
                  </div>
                  <div class="h4 mb-0"></div>
                  <small class="text-muted text-uppercase font-weight-bold">{{ __('app.home.membersonline') }}</small>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-3">
              <div class="card text-white bg-info">
                <div class="card-body">
                  <div class="h1 text-muted text-right mb-4">
                    <i class="icon-people"></i>
                  </div>
                  <div class="h4 mb-0"></div>
                  <small class="text-muted text-uppercase font-weight-bold">{{ __('app.home.totalturnover') }}</small>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-3">
              <div class="card text-white bg-info">
                <div class="card-body">
                  <div class="h1 text-muted text-right mb-4">
                    <i class="icon-people"></i>
                  </div>
                  <div class="h4 mb-0"></div>
                  <small class="text-muted text-uppercase font-weight-bold">{{ __('app.home.totalwinloss') }}</small>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-3">
              <div class="card text-white bg-info">
                <div class="card-body">
                  <div class="h1 text-muted text-right mb-4">
                    <i class="icon-people"></i>
                  </div>
                  <div class="h4 mb-0"></div>
                  <small class="text-muted text-uppercase font-weight-bold">{{ __('app.home.totalmemberbet') }}</small>
                </div>
              </div>
            </div>
          </div>
          
	</div>
</div>

@endsection
