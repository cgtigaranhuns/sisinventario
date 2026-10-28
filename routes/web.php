<?php

use App\Http\Controllers\BemRelatorioController;
use App\Http\Controllers\ConferenciaRelatorioController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/relatorios/bens/pdf', [BemRelatorioController::class, 'pdf'])
    ->middleware('auth')
    ->name('relatorios.bens.pdf');

Route::get('/relatorios/conferencia/{inventario}/pdf', [ConferenciaRelatorioController::class, 'pdf'])
    ->middleware('auth')
    ->name('relatorios.conferencia.pdf');

Route::get('/', function () {
    //view('welcome');
    return  redirect('/admin'); })->name('login');
