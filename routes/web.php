<?php

use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;
use Rap2hpoutre\LaravelLogViewer\LogViewerController;

/* -------------------------------- Start Logs ------------------------------------------------ */

Route::get('logs', [LogViewerController::class, 'index']);

/* -------------------------------- End Logs  ------------------------------------------------ */


/* -------------------------------- Start Auth ------------------------------------------------ */

Route::get('/', [LoginController::class, 'login'])->name('get_login');
Route::get('/redirect', [LoginController::class, 'redirector'])->name('redirector');
Route::get('/logout', [LoginController::class, 'getLogout'])->name('logout');


/* -------------------------------- End Auth ------------------------------------------------ */