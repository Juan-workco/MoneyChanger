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

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
 */
// Route::get('/', 'Auth\LoginController@showLoginForm')->name('/');

Route::get('/', function() {

	if(Auth::user())
	{            
		return redirect('/home');
	}
	else
	{
		return redirect(route('login'));  
	}
});

Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');
Route::get('/auth/changepassword', 'ViewControllers\PasswordViewController@changePasswordView')->name('changepassword');
Route::post('/currency', 'Currency@setCurrency')->name('currency');

// ajax
Route::post('/ajax/accounts/change_password', 'PasswordController@changePassword');

/*
|--------------------------------------------------------------------------
| Locale Routes
|--------------------------------------------------------------------------
 */

Route::get('/locale', function () {
	return abort(404);
});
Route::post('/locale', 'Locale@setLocale')->name('locale');

/*
|--------------------------------------------------------------------------
| Home Routes
|--------------------------------------------------------------------------
 */
Route::get('/home', 'ViewControllers\HomeViewController@index');

/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
 */

Route::get('/dashboard', 'ViewControllers\DashboardViewController@index');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
 */

//admin roles
Route::get('/admins/roles', 'ViewControllers\AdminViewController@rolesDetails');
Route::get('/admins/roles/new', 'ViewControllers\AdminViewController@newRoles');
Route::get('/admins/roles/permission', 'ViewControllers\AdminViewController@editRoles');

//ajax admin roles
Route::post('/ajax/admins/roles/create', 'ViewControllers\AdminViewController@createRoles');
Route::get('/ajax/admins/roles/list', 'ViewControllers\AdminViewController@getRolesList');
Route::post('/ajax/admins/roles/delete', 'ViewControllers\AdminViewController@deleteRole');
Route::get('/ajax/admins/roles/permission', 'ViewControllers\AdminViewController@getRolesPermission');
Route::post('/ajax/admins/roles/update', 'ViewControllers\AdminViewController@editRolesPermission');