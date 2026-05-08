<?php

namespace App\Console\Commands;

use App\Models\LoyerReference;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportLoyersOll extends Command
{
    protected $signature   = 'loyers:import {--truncate : Vider la table avant import}';
    protected $description = 'Importe les références de loyers OLL depuis data.gouv.fr';

    // Mapping typo → nb_pieces
    private const TYPO_MAP = [
        'T1'  => 1,
        'T2'  => 2,
        'T3'  => 3,
        'T4'  => 4,
        'T4+' => 4,
        'T5'  => 4,
        'T5+' => 4,
        'T6'  => 4,
        'T6+' => 4,
    ];

    public function handle(): int
    {
        $client = new Client(['timeout' => 30, 'verify' => false]);

        // ── 1. Découverte dynamique de l'URL CSV ──────────────────────────────
        $apiUrl = 'https://www.data.gouv.fr/api/1/datasets/resultats-nationaux-des-observatoires-locaux-des-loyers/';
        $this->info('Appel API data.gouv.fr…');

        try {
            $response = $client->get($apiUrl, ['headers' => ['Accept' => 'application/json']]);
            $meta     = json_decode((string) $response->getBody(), true);
        } catch (\Throwable $e) {
            $this->error('Erreur API data.gouv.fr : ' . $e->getMessage());
            return 1;
        }

        $csvUrl = null;
        foreach ($meta['resources'] ?? [] as $resource) {
            $fmt = strtolower($resource['format'] ?? '');
            $ttl = strtolower($resource['title']  ?? '');
            if (str_contains($fmt, 'csv') || str_ends_with($ttl, '.csv')) {
                $csvUrl = $resource['url'];
                break;
            }
        }

        if (!$csvUrl) {
            $this->error('Aucun fichier CSV trouvé dans le dataset OLL.');
            return 1;
        }
        $this->info("CSV trouvé : {$csvUrl}");

        // ── 2. Téléchargement ─────────────────────────────────────────────────
        $this->info('Téléchargement du CSV…');
        $tmpFile = tempnam(sys_get_temp_dir(), 'oll_') . '.csv';

        try {
            $client->get($csvUrl, ['sink' => $tmpFile, 'timeout' => 120]);
        } catch (\Throwable $e) {
            $this->error('Erreur téléchargement : ' . $e->getMessage());
            @unlink($tmpFile);
            return 1;
        }

        $size = filesize($tmpFile);
        $this->info(sprintf('Fichier téléchargé : %s ko', number_format($size / 1024, 0, ',', ' ')));

        // ── 3. Lecture et détection des colonnes ──────────────────────────────
        $fh = fopen($tmpFile, 'r');
        if (!$fh) {
            $this->error('Impossible d\'ouvrir le fichier temporaire.');
            return 1;
        }

        // Détecte le séparateur (virgule ou point-virgule)
        $firstLine = fgets($fh);
        rewind($fh);
        $sep = substr_count($firstLine, ';') >= substr_count($firstLine, ',') ? ';' : ',';

        $rawHeaders = fgetcsv($fh, 0, $sep);
        if (!$rawHeaders) {
            $this->error('Impossible de lire les en-têtes CSV.');
            fclose($fh);
            @unlink($tmpFile);
            return 1;
        }

        // Normalisation des headers : minuscules, trim, accents translit
        $headers = array_map(function (string $h): string {
            $h = iconv('UTF-8', 'ASCII//TRANSLIT', trim($h));
            return strtolower((string) $h);
        }, $rawHeaders);

        $this->line('En-têtes détectés : ' . implode(', ', $headers));

        // Résolution des colonnes par patterns
        $col = function (array $patterns) use ($headers): ?int {
            foreach ($patterns as $p) {
                foreach ($headers as $i => $h) {
                    if (str_contains($h, $p)) {
                        return $i;
                    }
                }
            }
            return null;
        };

        $iAnnee  = $col(['annee', 'annee', 'year']);
        $iSect   = $col(['secteur', 'zone', 'agglom', 'commune', 'ville', 'libelle']);
        $iTypo   = $col(['typo_logt', 'type_logement', 'type_logt', 'typo', 'type']);
        $iLm2Med = $col(['lm2_med', 'loyer_m2_med', 'loyer_m2', 'lm2med', 'lm2']);
        $iLMed   = $col(['lmed', 'loyer_med', 'loyer_median', 'loyermed']);
        $iNbObs  = $col(['nb_obs', 'nombre_obs', 'nbobs', 'nobs', 'n_obs']);

        // Vérifications obligatoires
        $missing = [];
        if ($iAnnee  === null) $missing[] = 'annee';
        if ($iSect   === null) $missing[] = 'secteur';
        if ($iTypo   === null) $missing[] = 'typo_logt';
        if ($iLm2Med === null) $missing[] = 'loyer_m2_median';

        if (!empty($missing)) {
            $this->error('Colonnes non trouvées : ' . implode(', ', $missing));
            $this->line('Colonnes disponibles : ' . implode(', ', $headers));
            fclose($fh);
            @unlink($tmpFile);
            return 1;
        }

        $this->info(sprintf(
            'Colonnes mappées → annee[%d] secteur[%d] typo[%d] lm2_med[%d] lmed[%s] nb_obs[%s]',
            $iAnnee, $iSect, $iTypo, $iLm2Med,
            $iLMed  !== null ? $iLMed  : '–',
            $iNbObs !== null ? $iNbObs : '–'
        ));

        // ── 4. Truncate optionnel ─────────────────────────────────────────────
        if ($this->option('truncate')) {
            DB::table('loyer_references')->truncate();
            $this->line('Table loyer_references vidée.');
        }

        // ── 5. Import par chunks ──────────────────────────────────────────────
        $batchSize = 500;
        $batch     = [];
        $imported  = 0;
        $skipped   = 0;
        $now       = now()->toDateTimeString();

        while (($row = fgetcsv($fh, 0, $sep)) !== false) {
            // Ignore lignes trop courtes
            if (count($row) <= max($iAnnee, $iSect, $iTypo, $iLm2Med)) {
                $skipped++;
                continue;
            }

            $annee  = (int) trim($row[$iAnnee] ?? '');
            $secteur = trim($row[$iSect]  ?? '');
            $typo   = strtoupper(trim($row[$iTypo]  ?? ''));
            $lm2    = $row[$iLm2Med] !== '' ? (float) str_replace(',', '.', $row[$iLm2Med]) : null;
            $lmed   = $iLMed  !== null && $row[$iLMed]  !== '' ? (float) str_replace(',', '.', $row[$iLMed]) : null;
            $nbObs  = $iNbObs !== null && $row[$iNbObs] !== '' ? (int)   $row[$iNbObs] : null;

            if (!$annee || !$secteur || !$typo) {
                $skipped++;
                continue;
            }

            // Mapping typo → nb_pieces
            $nbPieces = null;
            foreach (self::TYPO_MAP as $key => $val) {
                if (str_starts_with($typo, $key)) {
                    $nbPieces = $val;
                    break;
                }
            }
            // Skip si typo totalement inconnue
            if ($nbPieces === null && !preg_match('/^T\d/', $typo)) {
                $skipped++;
                continue;
            }

            $villeNorm = LoyerReference::normalizeVille($secteur);

            $batch[] = [
                'annee'           => $annee,
                'secteur'         => $secteur,
                'ville_normalized'=> $villeNorm,
                'typo_logt'       => $typo,
                'nb_pieces'       => $nbPieces,
                'loyer_m2_median' => $lm2,
                'loyer_median'    => $lmed,
                'nb_obs'          => $nbObs,
                'created_at'      => $now,
                'updated_at'      => $now,
            ];

            if (count($batch) >= $batchSize) {
                DB::table('loyer_references')->upsert(
                    $batch,
                    ['annee', 'secteur', 'typo_logt'],
                    ['ville_normalized', 'nb_pieces', 'loyer_m2_median', 'loyer_median', 'nb_obs', 'updated_at']
                );
                $imported += count($batch);
                $this->line(sprintf('%s lignes importées…', number_format($imported, 0, ',', ' ')));
                $batch = [];
            }
        }

        // Dernier batch
        if (!empty($batch)) {
            DB::table('loyer_references')->upsert(
                $batch,
                ['annee', 'secteur', 'typo_logt'],
                ['ville_normalized', 'nb_pieces', 'loyer_m2_median', 'loyer_median', 'nb_obs', 'updated_at']
            );
            $imported += count($batch);
        }

        fclose($fh);
        @unlink($tmpFile);

        $this->info(sprintf(
            'Import OLL terminé : %s lignes importées, %s ignorées.',
            number_format($imported, 0, ',', ' '),
            number_format($skipped,  0, ',', ' ')
        ));

        return 0;
    }
}
