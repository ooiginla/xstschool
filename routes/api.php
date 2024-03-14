<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SmsController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->middleware('app.api.auth')->group(function () {
    Route::get('/testing', function (Request $request) {
        return "hello";
    });

    Route::prefix('notifications')->group(function(){
        Route::post('/sms/single', [SmsController::class, 'sendSingle'])->name('sms.sendsingle');
    });
});