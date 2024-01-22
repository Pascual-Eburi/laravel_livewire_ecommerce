<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminController;


Route::prefix('admin')->name('admin')->group( function (){

    // not authenticated
    Route::middleware(['guest:admin'])->group( function (){
        Route::view('/login', 'backend.pages.auth.login')
            ->name('login');
    });

    // authenticated admins
    Route::middleware(['auth:admin'])->group( function (){
        Route::view('/home', 'backend.pages.admin.home')->name('home');
    });
});


?>
