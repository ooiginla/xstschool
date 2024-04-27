<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\AdapterController;
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

Route::prefix('v1/adapters')->group(function(){
    Route::post('/purchase', [AdapterController::class, 'processRequest'])->name('adapater.processRequest');
    Route::post('/status', [AdapterController::class, 'statusRequest'])->name('adapater.status');
});

Route::prefix('v1/transformers')->group(function()
{
    Route::any('{provider}/notifications/sms/send', [SmsController::class, 'transform'])->name('sms.send.transform');
});

Route::prefix('v1')->middleware('app.api.auth')->group(function () 
{
    Route::prefix('business')->group(function() {
        Route::get('/account/balance', [GeneralController::class, 'getBalance'])->name('account.balance');
    });

    Route::prefix('notifications')->group(function() {
        Route::post('/sms/send', [SmsController::class, 'send'])->name('sms.send');
    });

    Route::prefix('transactions')->group(function() {
        Route::post('/retry', [GeneralController::class, 'retry'])->name('transactions.retry');
        Route::get('/status', [GeneralController::class, 'status'])->name('transactions.status');
    });
});