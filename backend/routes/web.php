<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IntelligenceController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\SelectionHistoryController;
use Illuminate\Support\Facades\Route;

// The dashboard is the CoJ-MIP landing page.
Route::redirect('/', '/dashboard');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/selection-history', [SelectionHistoryController::class, 'index'])->name('selection.history');

Route::get('/intelligence', [IntelligenceController::class, 'index'])->name('intelligence.search');

// Newsletter studio (Phase 7).
Route::get('/newsletter', [NewsletterController::class, 'index'])->name('newsletter.studio');

Route::post('/newsletter/compose', [NewsletterController::class, 'compose'])->name('newsletter.compose');

Route::get('/newsletter/layout/{layout}', [NewsletterController::class, 'setLayout'])->name('newsletter.layout');

Route::get('/newsletter/pdf', [NewsletterController::class, 'downloadPdf'])->name('newsletter.pdf');
