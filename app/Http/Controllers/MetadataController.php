<?php

namespace App\Http\Controllers;

use App\Services\MetadataFetcherService;
use Illuminate\Http\Request;

class MetadataController extends Controller
{
    public function __invoke(Request $request, MetadataFetcherService $fetcher)
    {
        $request->validate(['url' => 'required|string']);
        return response()->json($fetcher->fetch($request->url));
    }
}
