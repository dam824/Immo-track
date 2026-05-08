<?php

namespace App\Http\Controllers;

use App\Models\DvfTransaction;
use App\Services\DvfService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DvfController extends Controller
{
    public function marche(Request $request, DvfService $dvf): JsonResponse
    {
        $ville      = trim($request->string('ville')->toString());
        $superficie = $request->float('superficie') ?: null;
        $prixBien   = $request->float('prix_bien') ?: null;

        if (!$ville) {
            return response()->json(['data_disponible' => false, 'error' => 'Ville manquante']);
        }

        return response()->json($dvf->getMarcheLocal($ville, $superficie, $prixBien));
    }

    public function stats(): JsonResponse
    {
        $total = DvfTransaction::count();

        $byDept = DB::table('dvf_transactions')
            ->selectRaw('SUBSTRING(code_commune, 1, 2) as dept, COUNT(*) as nb, MIN(date_mutation) as date_min, MAX(date_mutation) as date_max')
            ->groupByRaw('SUBSTRING(code_commune, 1, 2)')
            ->orderBy('dept')
            ->get()
            ->keyBy('dept');

        $deptNames = [
            '77' => 'Seine-et-Marne',
            '78' => 'Yvelines',
            '92' => 'Hauts-de-Seine',
            '93' => 'Seine-Saint-Denis',
            '94' => 'Val-de-Marne',
            '95' => "Val-d'Oise",
        ];

        $depts = [];
        foreach ($deptNames as $code => $name) {
            $row = $byDept[$code] ?? null;
            $depts[] = [
                'code'     => $code,
                'nom'      => $name,
                'nb'       => $row ? (int) $row->nb : 0,
                'date_min' => $row?->date_min,
                'date_max' => $row?->date_max,
            ];
        }

        return response()->json([
            'total' => $total,
            'depts' => $depts,
        ]);
    }

    public function parametres(): \Inertia\Response
    {
        return Inertia::render('Parametres/Index');
    }

    public function sync(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $depts    = preg_replace('/[^0-9,]/', '', $request->string('dept', '92,93,94,95,77,78')->toString());
        $year     = (int) ($request->integer('year') ?: date('Y'));
        $truncate = $request->boolean('truncate', true);

        return response()->stream(function () use ($depts, $year, $truncate) {
            if (ob_get_level()) ob_end_clean();
            ob_implicit_flush(true);
            set_time_limit(600);

            $phpBin  = PHP_BINARY;
            $artisan = base_path('artisan');

            $flags  = " --dept={$depts} --year={$year}";
            $flags .= $truncate ? ' --truncate' : '';

            $cmd = escapeshellarg($phpBin) . ' ' . escapeshellarg($artisan) . " dvf:import{$flags} 2>&1";

            $process = proc_open($cmd, [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
            ], $pipes, base_path());

            if (!is_resource($process)) {
                echo "data: " . json_encode(['error' => "Impossible de démarrer l'import"]) . "\n\n";
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
                    $parts  = explode("\n", $buffer);
                    $buffer = array_pop($parts);
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
