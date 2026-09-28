<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', function () {
    //view('welcome');
    return  redirect('/admin'); })->name('login');
