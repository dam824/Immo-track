<?php

namespace App\Http\Controllers;

use App\Services\LoyerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LoyerController extends Controller
{
    public function __construct(private readonly LoyerService $loyers) {}

    /**
     * GET /api/loyers/estimate?ville=Paris&nb_pieces=2&annee=2023
     */
    public function estimate(Request $request): JsonResponse
    {
        $ville = trim($request->string('ville')->toString());
        if (!$ville) {
            return response()->json(['disponible' => false, 'error' => 'Paramètre ville manquant.'], 422);
        }

        $nbPieces  = $request->has('nb_pieces')  ? (int)   $request->integer('nb_pieces')  : null;
        $superficie = $request->has('superficie') ? (float) $request->float('superficie')   : null;
        $annee     = $request->has('annee')       ? (int)   $request->integer('annee')      : null;

        return response()->json($this->loyers->estimate($ville, $nbPieces, $superficie, $annee));
    }

    /**
     * GET /api/loyers/stats
     */
    public function stats(): JsonResponse
    {
        return response()->json($this->loyers->stats());
    }

    /**
     * GET /api/loyers/sync  — lance loyers:import en SSE (comme dvf:sync)
     */
    public function sync(Request $request): StreamedResponse
    {
        $truncate = $request->boolean('truncate', false);

        return response()->stream(function () use ($truncate) {
            if (ob_get_level()) ob_end_clean();
            ob_implicit_flush(true);
            set_time_limit(300);

            $phpBin  = PHP_BINARY;
            $artisan = base_path('artisan');
            $flags   = $truncate ? ' --truncate' : '';
            $cmd     = escapeshellarg($phpBin) . ' ' . escapeshellarg($artisan) . " loyers:import{$flags} 2>&1";

            $process = proc_open($cmd, [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
            ], $pipes, base_path());

            if (!is_resource($process)) {
                echo "data: " . json_encode(['error' => "Impossible de démarrer l'import OLL"]) . "\n\n";
                flush();
                return;
            }

            fclose($pipes[0]);
            stream_set_blocking($pipes[1], false);

            $buffer = '';
            while (true) {
                $chunk = fread($pipes[1], 4096);

                if ($chunk !== false && $chunk !== '') {
                    $buffer .= $chunk;
                    $parts   = explode("\n", $buffer);
                    $buffer  = array_pop($parts);
                    foreach ($parts as $line) {
                        $line = rtrim($line);
                        if ($line !== '') {
                            echo "data: " . json_encode(['line' => $line]) . "\n\n";
                            flush();
                        }
                    }
                }

                $status = proc_get_status($process);
                if (!$status['running']) {
                    if (trim($buffer) !== '') {
                        echo "data: " . json_encode(['line' => rtrim($buffer)]) . "\n\n";
                    }
                    break;
                }

                usleep(50000);
            }

            fclose($pipes[1]);
            proc_close($process);

            echo "data: " . json_encode(['done' => true]) . "\n\n";
            flush();

        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache, no-store',
            'X-Accel-Buffering' => 'no',
            'Connection'        => 'keep-alive',
        ]);
    }
}
