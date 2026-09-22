<?php

namespace App\Http\Controllers;

class IntelligenceController extends Controller
{
    /**
     * Municipal intelligence search screen. The page is a thin client for
     * the intelligence search API; all ranking logic lives in the
     * application layer, not here.
     */
    public function index()
    {
        return view('intelligence');
    }
}