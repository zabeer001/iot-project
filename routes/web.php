<?php

use App\Http\Controllers\CompanyAreaController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DeviceController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('login');
});

// setup
Route::group(['prefix' => 'setup', 'middleware' => 'auth'], function () {
    Route::get('/areas', [CompanyAreaController::class, 'index'])->name('setup.areas')->middleware(auth::class);
    Route::get('/areaList', [CompanyAreaController::class, 'areaList'])->name('setup.areaList');
});

// company
Route::group(['prefix' => 'companies', 'middleware' => 'auth'], function () {
    Route::get('/getCompanyNames', [CompanyController::class, 'getCompanyNames'])->name('companies.getCompanyNames');
    Route::get('/', [CompanyController::class, 'index'])->name('companies.index');
    Route::get('/edit/{companyId}', [CompanyController::class, 'edit'])->name('companies.edit');
    Route::post('/store', [CompanyController::class, 'store'])->name('companies.store');
    Route::post('/update', [CompanyController::class, 'update'])->name('companies.update');
    Route::get('/remove/{companyId}', [CompanyController::class, 'destroy'])->name('companies.destroy');
    Route::get('/companyList', [CompanyController::class, 'companyList'])->name('companies.companyList');

    Route::get('/select/{companyId}', [CompanyController::class, 'selectCompany'])->name('companies.select');


    // selected company section menus
    // Route::group(['prefix' => 'section', 'middleware' => 'auth'], function () {
    //     Route::get('/profile/{companyId}', [CompanyController::class, 'edit'])->name('companies.section.profile');
    // });
});



//for testing select companu dynamically
Route::get('/destroy_company_session', function () {
    session()->forget('selectedCompanyId');
});
Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::group(['middleware' => 'auth'], function () {

    Route::get('/devices', [DeviceController::class, 'index'])->name('zabeer.device');
});


Route::group(['prefix' => 'section', 'middleware' => 'auth'], function () {
    Route::get('/profile', [CompanyController::class, 'edit'])->name('companies.section.profile');
});
