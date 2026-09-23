<?php

namespace App\Http\Controllers;

use App\Application\Dashboard\DashboardService;

/**
 * Full selection decision history. All data comes from the persisted
 * SelectionDecisionRepository through the same Dashboard application service;
 * no second history mechanism exists.
 */
class SelectionHistoryController extends Controller
{
    public function index(DashboardService $dashboard)
    {
        $history = $dashboard->selectionHistory();

        return view('selection-history', [
            'groups' => $history['groups'],
            'total' => $history['total'],
            'has_history' => $history['has_history'],
        ]);
    }
}