<?php

namespace App\Http\Controllers;

use App\Services\StatsService;
use Inertia\Inertia;

class StatsController extends Controller
{
    public function __invoke(StatsService $stats)
    {
        return Inertia::render('Stats/Index', [
            'stats_villes' => $stats->parVille(),
            'global'       => $stats->global(),
        ]);
    }

    public function parVille(StatsService $stats)
    {
        return response()->json($stats->parVille());
    }
}
