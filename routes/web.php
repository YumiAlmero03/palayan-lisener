<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers;

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
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware(['auth'])->prefix('admin')->group(function () {

    Route::middleware(['auth'])->prefix('biometrics')->group(function () {
        Route::get('','Biometrics\ApiController@index')->name('biometrics.index');
        Route::get('table','Biometrics\ApiController@getBiometric')->name('biometrics.table');
    });
    
    Route::middleware(['auth'])->prefix('attendance')->group(function () {
        Route::get('','Attendance\ApiController@index')->name('biometrics.index');
        Route::get('table','Attendance\ApiController@index')->name('biometrics.table');
    });

});
// schedule tester
Route::get('test','Biometrics\ScheduleController@getBiometric')->name('test');


