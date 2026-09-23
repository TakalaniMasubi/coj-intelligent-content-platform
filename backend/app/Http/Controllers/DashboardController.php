<?php

namespace App\Http\Controllers;

use App\Application\Dashboard\DashboardService;

/**
 * CoJ-MIP executive dashboard. This controller is intentionally thin: the
 * dashboard view-model comes from the Dashboard application service, which
 * reads exclusively through the existing repository contracts and
 * intelligence services.
 */
class DashboardController extends Controller
{
    public function index(DashboardService $dashboard)
    {
        return view('dashboard', $dashboard->metrics());
    }
}