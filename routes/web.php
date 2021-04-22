<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\SlaughterController;
use Illuminate\Support\Facades\Route;
use Rap2hpoutre\LaravelLogViewer\LogViewerController;

/* -------------------------------- Start Logs ------------------------------------------------ */

Route::get('logs', [LogViewerController::class, 'index']);

/* -------------------------------- End Logs  ------------------------------------------------ */


/* -------------------------------- Start Auth ------------------------------------------------ */

Route::get('/', [LoginController::class, 'login'])->name('login');
Route::post('/', [LoginController::class, 'processLogin'])->name('process_login');
Route::get('/redirect', [LoginController::class, 'redirector'])->name('redirector');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

/* -------------------------------- End Auth ------------------------------------------------ */


/* -------------------------------- Start Slaughter ------------------------------------------------ */
Route::prefix('slaughter')->group(function () {
    Route::get('/dashboard', [SlaughterController::class, 'index'])->name('slaughter_dashboard');
    Route::get('/weigh', [SlaughterController::class, 'weigh'])->name('slaughter_weigh');
    Route::get('/weigh-data-ajax', [SlaughterController::class, 'loadWeighDataAjax']);
    Route::get('/next-receipt-ajax', [SlaughterController::class, 'nextReceiptAjax']);
    Route::post('/weigh-save', [SlaughterController::class, 'saveWeighData'])->name('save_weigh');
    Route::get('/receipts', [SlaughterController::class, 'receipts'])->name('receipts');
    Route::post('/receipts/import', [SlaughterController::class, 'importReceipts'])->name('receipts_import');
    Route::get('/report', [SlaughterController::class, 'slaughterReport'])->name('slaughter_report');
    Route::get('/configs', [SlaughterController::class, 'scaleConfigs'])->name('scale_configs');
    Route::post('/scale/update', [SlaughterController::class, 'updateScaleConfigs'])->name('update_scale_configs');
    Route::get('/comport-list', [SlaughterController::class, 'comportListApiService']);
    Route::get('/read-scale', [SlaughterController::class, 'readScaleApiService']);
});
/* -------------------------------- End Slaughter ------------------------------------------------ */