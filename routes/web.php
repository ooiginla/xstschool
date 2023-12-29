<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\VoteController;
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
Route::get('/', [WelcomeController::class, 'home'])->name('welcome.home');
Route::get('/vote/register', [VoteController::class, 'register'])->name('vote.register');
Route::post('/vote/register', [VoteController::class, 'postRegister'])->name('vote.register');
Route::get('/vote/{code}', [VoteController::class, 'vote'])->name('vote.vote');
Route::post('/vote/{code}', [VoteController::class, 'postVote'])->name('vote.postVote');
Route::get('/voting/result', [VoteController::class, 'result'])->name('vote.result');