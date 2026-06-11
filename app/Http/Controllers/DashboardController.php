<?php

namespace App\Http\Controllers;

use App\Actions\GetDashboardStats;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(GetDashboardStats $stats): Response
    {
        return Inertia::render('Welcome', $stats->handle());
    }
}
