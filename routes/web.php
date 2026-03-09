<?php

use Illuminate\Support\Facades\Route;
use App\Models\LogisticRequisition;
use App\Models\LogisticReceiving;
use App\Models\CattlePurchaseOrder;
use App\Models\CattleReceiving;
use App\Models\CattleWeighing;
use App\Models\AccountPayableInstallment;
use App\Http\Controllers\LogisticPoPrintController;
use App\Http\Controllers\BeefPrintController;

Route::get('/', function () {
    return view('dashboard');
});

// ==========================================
// KUMPULAN ROUTE PRINT & VOUCHER (PROTECTED)
// ==========================================
Route::middleware(['web', 'auth'])->group(function () {

    // ------------------------------------------
    // 1. MODUL LOGISTIC
    // ------------------------------------------
    Route::get('/print/logistic-request/{id}', function ($id) {
        $record = LogisticRequisition::with(['user', 'supplier', 'items.item'])->findOrFail($id);
        return view('print.logistic-request', compact('record'));
    })->name('print.logistic-request');

    Route::get('/print/logistic-po/{id}', [LogisticPoPrintController::class, 'print'])->name('print.logistic-po');

    Route::get('/print/logistic-receiving/{id}', function ($id) {
        $receiving = LogisticReceiving::with(['purchaseOrder', 'supplier', 'items.item'])->findOrFail($id);
        return view('print.logistic-receiving', compact('receiving'));
    })->name('print.logistic-receiving');


    // ------------------------------------------
    // 2. MODUL BEEF (DAGING)
    // ------------------------------------------
    Route::get('/print/beef-request/{id}', [BeefPrintController::class, 'printRequest'])->name('print.beef-request');

    Route::get('/print/beef-po/{id}', [BeefPrintController::class, 'printPO'])->name('print.beef-po');


    // ------------------------------------------
    // 3. MODUL CATTLE (SAPI HIDUP)
    // ------------------------------------------
    Route::get('/print/cattle-po/{id}', function ($id) {
        $po = CattlePurchaseOrder::with(['supplier', 'items.cattleCategory', 'creator'])->findOrFail($id);
        return view('print.cattle-po', compact('po'));
    })->name('print.cattle-po');

    Route::get('/print/cattle-receiving/{id}', function ($id) {
        $record = CattleReceiving::with(['supplier', 'purchaseOrder', 'items.category', 'creator'])->findOrFail($id);
        return view('print.cattle-receiving', compact('record'));
    })->name('print.cattle-receiving');

    // ROUTE BARU: CATTLE WEIGHING
    Route::get('/print/cattle-weighing/{id}', function ($id) {
        $record = CattleWeighing::with([
            'receiving.supplier',
            'receiving.purchaseOrder',
            'items.receivingItem',
            'creator'
        ])->findOrFail($id);

        // Arahkan ke file blade yang baru lu buat: resources/views/print/cattle-weighing.blade.php
        return view('print.cattle-weighing', compact('record'));
    })->name('print.weighing'); // Nama route tetap 'print.weighing' agar Action di Filament lu gak error


    // ------------------------------------------
    // 4. MODUL FINANCE (KEUANGAN)
    // ------------------------------------------
    Route::get('/vouchers/bank-out/{id}', function ($id) {
        $installment = AccountPayableInstallment::with(['payable.supplier', 'creator'])->findOrFail($id);
        return view('vouchers.bank-out', compact('installment'));
    })->name('vouchers.bank-out.print');

    // Tambahkan ini di dalam Route::middleware(['web', 'auth'])->group(...) di web.php
    Route::get('/print/carcass/{id}', function ($id) {
        $record = \App\Models\Carcass::with([
            'weighing.receiving.supplier',
            'items.weighingItem.receivingItem',
            'creator'
        ])->findOrFail($id);

        return view('print.carcass', compact('record'));
    })->name('print.carcass');
});
