<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Authentication Routes
Route::get('/login', 'AuthController@showLogin')->name('login');
Route::post('/login', 'AuthController@login');
Route::post('/logout', 'AuthController@logout')->name('logout');
Route::get('/password/reset', 'AuthController@showResetPassword')->name('password.reset');
Route::post('/password/update', 'AuthController@updatePassword')->name('password.update');

// Protected Routes
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/', 'DashboardController@index')->name('dashboard');
    Route::get('/dashboard', 'DashboardController@index');

    // Currency Management
    Route::resource('currencies', 'CurrencyController');
    Route::post('currencies/{id}/activate', 'CurrencyController@activate')->name('currencies.activate');
    Route::post('currencies/{id}/deactivate', 'CurrencyController@deactivate')->name('currencies.deactivate');

    // Customer Management
    Route::resource('customers', 'CustomerController');
    Route::get('customers/{id}/transactions', 'CustomerController@transactionHistory')->name('customers.transactions');

    // Exchange Rates
    Route::get('exchange-rates/get-active', 'ExchangeRateController@getActiveRate')->name('exchange-rates.get-active');
    Route::resource('exchange-rates', 'ExchangeRateController');


    // Transactions
    Route::post('transactions/bulk-update-status', 'TransactionController@bulkUpdateStatus')->name('transactions.bulk-update-status');
    Route::post('transactions/{id}/status', 'TransactionController@updateStatus')->name('transactions.update-status');
    Route::resource('transactions', 'TransactionController');
    Route::get('transactions-search', 'TransactionController@search')->name('transactions.search');

    // Roles & Permissions
    Route::resource('roles', 'RoleController')->middleware('permission:manage_roles');

    // User Management
    Route::resource('users', 'UserController')->middleware('permission:manage_users');

    // Reports
    Route::group(['middleware' => ['permission:view_reports']], function () {
        Route::get('reports/daily', 'ReportController@dailyReport')->name('reports.daily');
        Route::get('reports/balance-sheet', 'ReportController@balanceSheet')->name('reports.balance-sheet');
        Route::get('reports/profit-loss', 'ReportController@profitLoss')->name('reports.profit-loss');
        Route::get('reports/commission', 'ReportController@commissionReport')->name('reports.commission');
        Route::get('reports/export-pdf', 'ReportController@exportPDF')->name('reports.export-pdf');
        Route::get('reports/export-profit-loss-pdf', 'ReportController@exportProfitLossPDF')->name('reports.export-profit-loss-pdf');
    });

    // Settings
    Route::group(['middleware' => ['permission:manage_settings']], function () {
        Route::get('settings', 'SettingsController@index')->name('settings.index');
        Route::post('settings/general', 'SettingsController@updateGeneral')->name('settings.update-general');
        Route::get('settings/accounts', 'SettingsController@accounts')->name('settings.accounts');
        Route::post('settings/accounts', 'SettingsController@storeAccount')->name('settings.store-account');
        Route::put('settings/accounts/{id}', 'SettingsController@updateAccount')->name('settings.update-account');
        Route::delete('settings/accounts/{id}', 'SettingsController@deleteAccount')->name('settings.delete-account');
        Route::get('settings/payment-methods', 'SettingsController@paymentMethods')->name('settings.payment-methods');
        Route::post('settings/payment-methods', 'SettingsController@updatePaymentMethods')->name('settings.update-payment-methods');

    });

});
