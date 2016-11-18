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

Route::get('/clients/', 'nso@list_tenants');

Route::get('/clients/{tenant}', 'nso@get_tenant');

Route::get('/', function () {
    return view('welcome');
});
