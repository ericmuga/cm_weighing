<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\SlaughterController;
use Illuminate\Support\Facades\Route;
use Rap2hpoutre\LaravelLogViewer\LogViewerController;

/* -------------------------------- Start Logs ------------------------------------------------ */

Route::get('logs', [LogViewerController::class, 'index']);

/* -------------------------------- End Logs  ------------------------------------------------ */


/* -------------------------------- Start Auth ------------------------------------------------ */

Route::get('/', [LoginController::class, 'login'])->name('get_login');
Route::post('/', [LoginController::class, 'processLogin'])->name('process_login');
Route::get('/redirect', [LoginController::class, 'redirector'])->name('redirector');
Route::get('/logout', [LoginController::class, 'getLogout'])->name('logout');

/* -------------------------------- End Auth ------------------------------------------------ */


/* -------------------------------- Start Slaughter ------------------------------------------------ */
Route::prefix('slaughter')->group(function () {
    Route::get('/dashboard', [SlaughterController::class, 'index'])->name('slaughter_dashboard');
    Route::get('/weigh', [SlaughterController::class, 'weigh'])->name('slaughter_weigh');
    Route::get('/receipts', [SlaughterController::class, 'receipts'])->name('receipts');
    Route::get('/receipts/import', [SlaughterController::class, 'importReceipts'])->name('receipts_import');
    Route::get('/report', [SlaughterController::class, 'slaughterReport'])->name('slaughter_report');
    Route::get('/configs', [SlaughterController::class, 'scaleConfigs'])->name('scale_configs');
    Route::get('/scale/update', [SlaughterController::class, 'updateScaleConfigs'])->name('update_scale_configs');
});
/* -------------------------------- End Slaughter ------------------------------------------------ */