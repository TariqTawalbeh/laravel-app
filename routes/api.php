<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SubscriptionsController;
use App\Http\Controllers\AuthController;
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
Route::post('/register', [AuthController::class , 'register']);
Route::post('/login', [AuthController::class , 'login']);

Route::middleware('auth:sanctum')->prefix('v1')->group(function (){
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/subscribe', [SubscriptionsController::class , 'subscribe']);
    Route::post('/unsubscribe', [SubscriptionsController::class , 'unSubscribe']);
    Route::post('/subscriptioncallback', [SubscriptionsController::class , 'subscriptionCallback']);

});


