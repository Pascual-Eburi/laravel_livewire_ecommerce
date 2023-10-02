<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
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

Route::get('/', function () {
    return view('welcome');
});


Route::view('login', 'backend.pages.auth.login')->name('login');
Route::prefix('auth')->name('auth.')->group( function (){
    Route::post('/login', [UserController::class, 'login'])
            ->name('loginHandler');
});


Route::view('/home', 'home')->name('home');
