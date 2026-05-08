<?php

namespace App\Console\Commands;

use App\Models\DvfTransaction;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportDvfData extends Command
{
    protected $signature = 'dvf:import {--dept=93,94,95} {--year=2024} {--truncate}';
    protected $description = 'Import DVF transaction data from data.gouv.fr CSV files';

    public function handle(): int
    {
        $depts = explode(',', $this->option('dept'));
        $year  = $this->option('year');

        if ($this->option('truncate')) {
            DvfTransaction::truncate();
            $this->info('Table vidée.');
        }

        $client = new Client([
            'timeout'         => 300,
            'connect_timeout' => 15,
            'verify'          => false,
        ]);

        foreach ($depts as $dept) {
            $dept = trim($dept);
            $this->info("Traitement département {$dept}...");

            // Try requested year, fallback to year-1
            $tmpFile = sys_get_temp_dir() . "/dvf_{$dept}.csv.gz";
            $downloaded = false;

            foreach ([$year, (int)$year - 1] as $y) {
                $url = "https://files.data.gouv.fr/geo-dvf/latest/csv/{$y}/departements/{$dept}.csv.gz";
                try {
                    $this->info("Téléchargement: {$url}");
                    $client->get($url, ['sink' => $tmpFile]);
                    $downloaded = true;
                    break;
                } catch (\Throwable $e) {
                    $this->warn("  Année {$y} : " . $e->getMessage());
                }
            }

            if (!$downloaded) {
                $this->error("Fichier introuvable pour le département {$dept}");
                continue;
            }

            $handle = gzopen($tmpFile, 'rb');
            if (!$handle) {
                $this->error("Impossible d'ouvrir le fichier gz pour {$dept}");
                continue;
            }

            // Read headers
            $headerLine = gzgets($handle);
            $headers = str_getcsv(trim($headerLine), ',', '"');
            $idx = array_flip($headers);

            $required = ['id_mutation', 'date_mutation', 'valeur_fonciere', 'code_commune', 'nom_commune', 'type_local', 'surface_reelle_bati', 'nombre_pieces_principales'];
            foreach ($required as $col) {
                if (!isset($idx[$col])) {
                    $this->error("Colonne manquante: {$col}. Headers: " . implode(', ', $headers));
                    gzclose($handle);
                    continue 2;
                }
            }

            $batch = [];
            $count = 0;
            $seen  = []; // track id_mutation for deduplication

            while (($line = gzgets($handle)) !== false) {
                $row = str_getcsv(trim($line), ',', '"');
                if (count($row) < count($headers)) continue;

                $typLocal = $row[$idx['type_local']] ?? '';
                if ($typLocal !== 'Appartement') continue;

                $idMut = $row[$idx['id_mutation']] ?? '';
                if (isset($seen[$idMut])) continue; // skip duplicate mutations
                $seen[$idMut] = true;

                $valeur = (float) str_replace(',', '.', $row[$idx['valeur_fonciere']] ?? '0');
                $surf   = (float) str_replace(',', '.', $row[$idx['surface_reelle_bati']] ?? '0');

                if ($valeur <= 0 || $surf <= 0) continue;

                $prixM2 = round($valeur / $surf, 0);
                if ($prixM2 < 500 || $prixM2 > 15000) continue;

                $date = $row[$idx['date_mutation']] ?? null;
                if (!$date) continue;

                $batch[] = [
                    'code_commune'    => $row[$idx['code_commune']],
                    'nom_commune'     => mb_convert_encoding($row[$idx['nom_commune']] ?? '', 'UTF-8', 'UTF-8'),
                    'date_mutation'   => $date,
                    'valeur_fonciere' => $valeur,
                    'superficie'      => $surf,
                    'nb_pieces'       => isset($idx['nombre_pieces_principales']) && $row[$idx['nombre_pieces_principales']] !== ''
                                         ? (int) $row[$idx['nombre_pieces_principales']] : null,
                    'prix_m2'         => $prixM2,
                ];

                if (count($batch) >= 500) {
                    DB::table('dvf_transactions')->insert($batch);
                    $count += count($batch);
                    $batch = [];
                    if ($count % 2000 === 0) {
                        $this->line("  [{$dept}] {$count} enregistrements traités...");
                    }
                }
            }

            if ($batch) {
                DB::table('dvf_transactions')->insert($batch);
                $count += count($batch);
            }

            gzclose($handle);
            unlink($tmpFile);
            $this->newLine();
            $this->info("Département {$dept}: {$count} appartements importés.");
        }

        $this->info('Import terminé.');
        return 0;
    }
}
