<?php

use Illuminate\Support\Facades\Route;
use App\Models\LogisticRequisition;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/print-logistic-request/{id}', function ($id) {
    $record = LogisticRequisition::with(['user', 'supplier', 'items.item'])->findOrFail($id);
    return view('print.logistic-request', compact('record'));
})->name('print.logistic-request')->middleware('auth');
