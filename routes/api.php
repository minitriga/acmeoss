<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/tenants/{tenant}', 'nso@create_tenant');
Route::post('/tenants/{tenant}/networks/{network}', 'nso@create_network');
Route::post('/tenants/{tenant}/services/{service}', 'nso@create_service');

Route::delete('/tenants/{tenant}', 'nso@delete_tenant');
Route::delete('/tenants/{tenant}/networks/{network}', 'nso@delete_network');
Route::delete('/tenants/{tenant}/services/{service}', 'nso@delete_service');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');
