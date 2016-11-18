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

Route::post('/tenants/{tenant}', 'NSO@CreateTenant');
Route::post('/tenants/{tenant}/networks/{network}', 'NSO@CreateNetwork');
Route::post('/tenants/{tenant}/services/{service}', 'NSO@CreateService');

Route::delete('/tenants/{tenant}', 'NSO@DeleteTenant');
Route::delete('/tenants/{tenant}/networks/{network}', 'NSO@DeleteNetwork');
Route::delete('/tenants/{tenant}/services/{service}', 'NSO@DeleteService');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');
