<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\QAController;
use App\Http\Controllers\SlaughterController;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;
use Rap2hpoutre\LaravelLogViewer\LogViewerController;

/* -------------------------------- Start Logs ------------------------------------------------ */

Route::get('logs', [LogViewerController::class, 'index']);

/* -------------------------------- End Logs  ------------------------------------------------ */


/* -------------------------------- Start Auth ------------------------------------------------ */

Route::get('/', [LoginController::class, 'home'])->name('home');
Route::post('/', [LoginController::class, 'processLogin'])->name('process_login');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

/* -------------------------------- End Auth ------------------------------------------------ */


/* -------------------------------- Start Slaughter ------------------------------------------------ */
Route::prefix('slaughter')->group(function () {
    Route::get('/dashboard', [SlaughterController::class, 'index'])->name('slaughter_dashboard');
    Route::get('/weigh', [SlaughterController::class, 'weigh'])->name('slaughter_weigh');
    Route::get('/offals/{type}', [SlaughterController::class, 'weighOffals'])->name('slaughter_weigh_offals');
    Route::get('/table', [SlaughterController::class, 'weightsTabulate'])->name('weights.tabulate');
    Route::post('/save-offals-weights', [SlaughterController::class, 'saveOffalsWeights'])->name('slaughter_save_offals_weights');
    Route::post('/edit', [SlaughterController::class, 'edit'])->name('slaughter_edit');
    Route::get('/weigh-data-ajax', [SlaughterController::class, 'loadWeighDataAjax']);
    Route::get('/next-receipt-ajax', [SlaughterController::class, 'nextReceiptAjax']);
    Route::post('/weigh-save', [SlaughterController::class, 'saveWeighData'])->name('save_weigh');
    Route::get('/receipts/{filter?}', [SlaughterController::class, 'receipts'])->name('receipts');
    Route::post('/receipts/import', [SlaughterController::class, 'importReceipts'])->name('receipts_import');
    Route::get('/report/{filter?}', [SlaughterController::class, 'slaughterReport'])->name('slaughter_report');
    Route::post('/report/summary', [SlaughterController::class, 'slaughterSummaryReport'])->name('slaughter_summary_report');

    Route::get('/pending-etims', [SlaughterController::class, 'pendingEtimsData'])->name('pending_etims');
    Route::post('/update-pending-etims', [SlaughterController::class, 'updatePendingEtimsData'])->name('update_pending_etims');
    Route::post('/send-sms', [SlaughterController::class, 'sendSmsCurl'])->name('send_sms');
    Route::post('/update-sms-sent-status', [SlaughterController::class, 'updateSmsSentStatus'])->name('update_send_sms_status');
});
/* -------------------------------- End Slaughter ------------------------------------------------ */


/* -------------------------------- Start QA ------------------------------------------------ */
Route::prefix('QA')->group(function () {
    Route::get('/dashboard', [QAController::class, 'index'])->name('qa_dashboard');
    Route::get('/grading', [QAController::class, 'grade'])->name('qa_grading');
    Route::get('/grading/v2', [QAController::class, 'gradeV2'])->name('qa_grading_v2');
    Route::post('update/grade', [QAController::class, 'updateGrading'])->name('qa_update_grading');
    Route::post('run/grading-classes', [QAController::class, 'runGradingClasses'])->name('run_grading_classes');
    Route::post('update/grade/v2', [QAController::class, 'updateGradingV2'])->name('qa_update_grading_v2');
});
/* -------------------------------- End Slaughter ------------------------------------------------ */

/* -------------------------------- Start Stocks ------------------------------------------------ */
Route::prefix('stock')->group(function () {
    Route::get('/', [StockController::class, 'dashboard'])->name('stocks_dashboard');
    Route::get('/transfers/issue', [StockController::class, 'transfersIssue'])->name('stock_transfers_issue');
    Route::get('/transfers/receive', [StockController::class, 'transfersReceive'])->name('stock_transfers_receive');
    Route::post('/transfer/save', [StockController::class, 'saveTransfer'])->name('save_transfer');
    Route::post('/transfers/update', [StockController::class, 'transferUpdate'])->name('transfer_update');
    Route::get('/records', [StockController::class, 'stockTake'])->name('stock_take');
    Route::post('/records/save', [StockController::class, 'stockUpdate'])->name('stock_update');
});
/* -------------------------------- End Transfers ------------------------------------------------ */

/* -------------------------------- Scale Configs ------------------------------------------------ */
Route::prefix('scale')->group(function () {
    Route::get('/configs/{section}', [SlaughterController::class, 'scaleConfigs'])->name('scale_configs');
    Route::post('/update', [SlaughterController::class, 'updateScaleConfigs'])->name('update_scale_configs');
    Route::get('/comport-list', [SlaughterController::class, 'comportListApiService'])->name('comport_list');
    Route::get('/read', [SlaughterController::class, 'readScaleApiService'])->name('read_scale');
});
/* -------------------------------- End Scale Configs ------------------------------------------------ */