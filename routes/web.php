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

Route::get('/clients/', 'NSO@ListTenants');

Route::get('/clients/{tenant}', 'NSO@GetTenant');

Route::get('/', function () {
    return view('welcome');
});
