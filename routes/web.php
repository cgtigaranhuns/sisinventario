<?php

use App\Http\Controllers\BemRelatorioController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/relatorios/bens/pdf', [BemRelatorioController::class, 'pdf'])
    ->middleware('auth')
    ->name('relatorios.bens.pdf');

Route::get('/', function () {
    //view('welcome');
    return  redirect('/admin'); })->name('login');
