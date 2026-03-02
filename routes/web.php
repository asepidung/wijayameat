<?php

use Illuminate\Support\Facades\Route;
use App\Models\LogisticRequisition;
use App\Http\Controllers\LogisticPoPrintController;
use App\Http\Controllers\BeefPrintController;
use App\Models\CattlePurchaseOrder;

Route::get('/', function () {
    return view('dashboard');
});

Route::get('/print-logistic-request/{id}', function ($id) {
    $record = LogisticRequisition::with(['user', 'supplier', 'items.item'])->findOrFail($id);
    return view('print.logistic-request', compact('record'));
})->name('print.logistic-request')->middleware('auth');

// Route untuk Cetak PO Logistic
Route::get('/print/logistic-po/{id}', [LogisticPoPrintController::class, 'print'])->name('print.logistic-po');

// Rute Print Beef
Route::get('/print/beef-request/{id}', [BeefPrintController::class, 'printRequest'])->name('print.beef-request');
Route::get('/print/beef-po/{id}', [BeefPrintController::class, 'printPO'])->name('print.beef-po');

Route::get('/print/cattle-po/{id}', function ($id) {
    $po = CattlePurchaseOrder::with(['supplier', 'items.cattleCategory', 'creator'])->findOrFail($id);
    return view('print.cattle-po', compact('po'));
})->name('print.cattle-po');
