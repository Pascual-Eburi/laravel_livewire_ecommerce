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

Route::redirect('/', 'auth/login');
Route::redirect('/login', 'auth/login');

/**
 * Auth routes
 */
Route::middleware('PreventBackHistory')->prefix('auth')->name('auth.')->group( function (){
    Route::view('/login', 'backend.pages.auth.login')->name('login');
    Route::post('/login', [UserController::class, 'login'])
            ->name('loginHandler');
    Route::post('/logout', [UserController::class, 'logout'])
            ->name('logoutHandler');
});


Route::middleware(['auth', 'PreventBackHistory'])->group( function (){
    Route::view('/home', 'home')->name('home');

});
