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

Route::post('/tenants/{tenant}', 'NSO@create_tenant');
Route::post('/tenants/{tenant}/networks/{network}', 'NSO@create_network');
Route::post('/tenants/{tenant}/services/{service}', 'NSO@create_service');

Route::delete('/tenants/{tenant}', 'NSO@delete_tenant');
Route::delete('/tenants/{tenant}/networks/{network}', 'NSO@delete_network');
Route::delete('/tenants/{tenant}/services/{service}', 'NSO@delete_service');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');
