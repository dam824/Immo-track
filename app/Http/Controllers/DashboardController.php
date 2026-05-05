<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Services\StatsService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(StatsService $stats)
    {
        return Inertia::render('Dashboard/Index', [
            'stats'            => $stats->global(),
            'recent_achats'    => Listing::achats()->latest()->limit(5)->get(),
            'recent_locations' => Listing::locations()->latest()->limit(5)->get(),
        ]);
    }
}
