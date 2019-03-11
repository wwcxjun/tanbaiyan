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

/*
|--------------------------------------------------------------------------
| 坦白言——小程序后端
| www.tanbaiyan.com
| 觉得不错的话 记得在Github上点个star哦~
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', 'Api\LoginController@login');

Route::post('/send', 'Api\SendController@store');

Route::post('/look', 'Api\LookController@index');

Route::get('/qrcode/{token}', 'Api\QrcodeController@index');

Route::get('/test', 'Api\QrcodeController@qrcode');