<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IntelligenceController;
use App\Http\Controllers\SelectionHistoryController;
use Illuminate\Support\Facades\Route;

// The dashboard is the CoJ-MIP landing page.
Route::redirect('/', '/dashboard');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/selection-history', [SelectionHistoryController::class, 'index'])->name('selection.history');

Route::get('/intelligence', [IntelligenceController::class, 'index'])->name('intelligence.search');