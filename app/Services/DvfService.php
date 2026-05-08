<?php

namespace App\Services;

use App\Models\DvfTransaction;
use GuzzleHttp\Client;
use Carbon\Carbon;

class DvfService
{
    public function getInseeCode(string $ville): ?string
    {
        try {
            $client = new Client(['timeout' => 5, 'verify' => false]);
            $resp = $client->get('https://geo.api.gouv.fr/communes', [
                'query' => ['nom' => $ville, 'fields' => 'code,nom', 'boost' => 'population', 'limit' => 5]
            ]);
            $data = json_decode($resp->getBody()->getContents(), true);
            return $data[0]['code'] ?? null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function getMarcheLocal(string $ville, ?float $superficie, ?float $prixBien = null): array
    {
        $inseeCode = $this->getInseeCode($ville);
        if (!$inseeCode) {
            return ['data_disponible' => false, 'error' => 'Ville non trouvée', 'ville' => $ville];
        }

        $query = DvfTransaction::where('code_commune', $inseeCode)
            ->where('date_mutation', '>=', Carbon::now()->subYears(3))
            ->whereBetween('prix_m2', [500, 15000]);

        if ($superficie && $superficie > 0) {
            $query->whereBetween('superficie', [$superficie * 0.7, $superficie * 1.4]);
        }

        $rows = $query->orderByDesc('date_mutation')->limit(200)
            ->get(['prix_m2', 'date_mutation', 'valeur_fonciere', 'superficie', 'nb_pieces'])
            ->toArray();

        if (count($rows) < 3) {
            return [
                'data_disponible'  => false,
                'error'            => 'Données insuffisantes (< 3 transactions). Importez les données avec: php artisan dvf:import',
                'ville'            => $ville,
                'code_commune'     => $inseeCode,
                'nb_transactions'  => count($rows),
            ];
        }

        // Median calculation
        $prices = array_column($rows, 'prix_m2');
        sort($prices);
        $count = count($prices);
        $mid = (int) floor($count / 2);
        $median = $count % 2 === 1 ? $prices[$mid] : ($prices[$mid - 1] + $prices[$mid]) / 2;
        $median = round($median, 0);

        // Comparison
        $prixM2Bien = null;
        $diffPct    = null;
        $color      = null;
        if ($prixBien && $superficie && $superficie > 0) {
            $prixM2Bien = round($prixBien / $superficie, 0);
            $diffPct = $median > 0 ? round(($prixM2Bien - $median) / $median * 100, 1) : null;
            if ($diffPct !== null) {
                $color = $diffPct < -5 ? 'green' : ($diffPct > 15 ? 'red' : 'orange');
            }
        }

        return [
            'data_disponible'  => true,
            'ville'            => $ville,
            'code_commune'     => $inseeCode,
            'nb_transactions'  => $count,
            'mediane_prix_m2'  => $median,
            'prix_m2_bien'     => $prixM2Bien,
            'diff_pct'         => $diffPct,
            'color'            => $color,
            'surface_filtre'   => $superficie ? round($superficie * 0.7) . '–' . round($superficie * 1.4) . ' m²' : null,
            'periode'          => Carbon::now()->subYears(3)->format('Y') . '–' . date('Y'),
            'transactions'     => array_slice($rows, 0, 10),
            'error'            => null,
        ];
    }
}
