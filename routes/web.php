<?php

use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

/* -------------------------------- Start Auth routes ------------------------------------------------ */

Route::get('/', [LoginController::class, 'login'])->name('get_login');


/* -------------------------------- End Auth routes ------------------------------------------------ */