<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::middleware('api')->prefix('auth')->namespace('Auth')->group(function() {
//     Route::post('login', 'AuthController@login')->name('login');
//     Route::post('logout', 'AuthController@logout');
//     Route::post('refresh', 'AuthController@refresh');
//     Route::post('me', 'AuthController@me');
// });

Route::middleware('api')->middleware('App\Http\Middleware\Login')->group(function() {
    Route::any('/', 'JobsController@test');
    Route::post('/queueMail', 'JobsController@queueMail')->name('queueMail');
    Route::post('/queueSMS', 'JobsController@queueSMS')->name('queueSMS');
    // Route::get('/queue', function (Request $request) {
    //     return response()->json(["Erreur : token ou IP non-autorisés"]);
    // });
    /**  exemple syntaxe : Route::get('/todos/{todo}', 'TodoController@show')->name('show'); **/

});
