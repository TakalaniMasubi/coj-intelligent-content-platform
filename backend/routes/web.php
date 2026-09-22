<?php

use App\Http\Controllers\IntelligenceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/intelligence', [IntelligenceController::class, 'index'])->name('intelligence.search');
