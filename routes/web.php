<?php

use Illuminate\Support\Facades\Route;
use App\Models\LogisticRequisition;
use App\Models\LogisticReceiving; // <-- JANGAN LUPA INI DITAMBAHIN
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

// ==========================================
// INI DIA RUTE BARU BUAT PRINT GR LOGISTIC
// ==========================================
Route::get('/print/logistic-receiving/{id}', function ($id) {
    // Tarik data GR beserta relasinya (PO, Supplier, dan Item)
    $receiving = LogisticReceiving::with(['purchaseOrder', 'supplier', 'items.item'])->findOrFail($id);

    // Nembak ke file blade print.logistic-receiving
    return view('print.logistic-receiving', compact('receiving'));
})->name('print.logistic-receiving')->middleware('auth');
