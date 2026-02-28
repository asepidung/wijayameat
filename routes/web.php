<?php

use Illuminate\Support\Facades\Route;
use App\Models\LogisticRequisition;
use App\Http\Controllers\LogisticPoPrintController;

Route::get('/', function () {
    return view('dashboard');
});

Route::get('/print-logistic-request/{id}', function ($id) {
    $record = LogisticRequisition::with(['user', 'supplier', 'items.item'])->findOrFail($id);
    return view('print.logistic-request', compact('record'));
})->name('print.logistic-request')->middleware('auth');

// Route untuk Cetak PO Logistic
Route::get('/print/logistic-po/{id}', [LogisticPoPrintController::class, 'print'])->name('print.logistic-po');
