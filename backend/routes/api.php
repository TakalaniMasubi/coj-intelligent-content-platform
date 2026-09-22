<?php

use App\Http\Controllers\Api\V1\IntelligenceSearchController;
use App\Http\Controllers\Api\V1\SystemController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CoJ-MIP API Routes — Version 1
|--------------------------------------------------------------------------
|
| Versioned API foundation under /api/v1.
|
| The intelligence, discovery, newsletter and repository endpoints are
| registered here as they are implemented. Foundation-level endpoint (ping)
| established in Phase 1; intelligence search added in Phase 4.
|
*/

Route::prefix('v1')->group(function () {
    Route::get('/ping', [SystemController::class, 'ping']);
    Route::post('/intelligence/search', [IntelligenceSearchController::class, 'search']);
});