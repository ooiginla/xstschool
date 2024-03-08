<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\ScreeningController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/testa', function(){
    return phpinfo();
});

Route::get('/', [WelcomeController::class, 'home'])->name('welcome.home');
Route::get('/registration', [WelcomeController::class, 'getRegistration'])->name('welcome.getRegistration');
Route::post('/registration', [WelcomeController::class, 'postRegistration'])->name('welcome.postRegistration');
Route::get('/vote/register', [VoteController::class, 'register'])->name('vote.register');
Route::post('/vote/register', [VoteController::class, 'postRegister'])->name('vote.registerPost');
Route::get('/vote/{code}', [VoteController::class, 'vote'])->name('vote.vote');
Route::post('/vote/{code}', [VoteController::class, 'postVote'])->name('vote.postVote');
Route::get('/voting/result', [VoteController::class, 'result'])->name('vote.result');
Route::get('/test/email', [WelcomeController::class, 'testEmail'])->name('welcome.testEmail');

Route::get('/screening/list', [ScreeningController::class, 'getGeneralList'])->name('screen.getGeneralList');
Route::get('/screen/members', [ScreeningController::class, 'getList'])->name('screen.getList');
Route::post('/screen/members', [ScreeningController::class, 'postList'])->name('screen.postList'); 