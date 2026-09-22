<?php

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
| PLANNED (Phases 2-10) and will be registered here as they are
| implemented. Only the foundation-level endpoint (ping) exists in
| Phase 1.
|
*/

Route::prefix('v1')->group(function () {
    Route::get('/ping', [SystemController::class, 'ping']);
});