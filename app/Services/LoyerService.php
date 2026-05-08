<?php

namespace App\Services;

use App\Models\LoyerReference;
use Illuminate\Support\Facades\DB;

class LoyerService
{
    /**
     * Estime le loyer de référence pour une ville et un nombre de pièces donnés.
     *
     * @param  string   $ville      Nom de la ville (libre)
     * @param  int|null $nbPieces   1, 2, 3 ou 4+
     * @param  int|null $annee      Année de référence (null = plus récente disponible)
     * @return array
     */
    public function estimate(string $ville, ?int $nbPieces = null, ?float $superficie = null, ?int $annee = null): array
    {
        $villeNorm = LoyerReference::normalizeVille($ville);

        $query = LoyerReference::where('ville_normalized', $villeNorm);

        if ($query->doesntExist()) {
            $query = LoyerReference::where('ville_normalized', 'LIKE', "%{$villeNorm}%");
        }

        if ($query->doesntExist()) {
            return ['disponible' => false];
        }

        if ($annee === null) {
            $annee = (clone $query)->max('annee');
        }

        $baseQuery = (clone $query)->where('annee', $annee);

        if ($nbPieces !== null) {
            $pieces = min($nbPieces, 4);
            $baseQuery->where('nb_pieces', $pieces);
        }

        $rows = $baseQuery->orderBy('typo_logt')->get();

        if ($rows->isEmpty()) {
            return ['disponible' => false];
        }

        $medianOf = function (string $col) use ($rows): ?float {
            $vals = $rows->pluck($col)->filter()->sort()->values();
            $n = $vals->count();
            if ($n === 0) return null;
            $mid = intdiv($n, 2);
            return $n % 2 === 1 ? (float) $vals[$mid] : ((float) $vals[$mid - 1] + (float) $vals[$mid]) / 2;
        };

        $lm2    = round($medianOf('loyer_m2_median') ?? 0, 2);
        $lmed   = round($medianOf('loyer_median')    ?? 0, 2);

        $loyerEstime = $superficie && $lm2 > 0
            ? (int) round($lm2 * $superficie)
            : ($lmed > 0 ? (int) round($lmed) : null);

        $loyerMeuble = $loyerEstime ? (int) round($loyerEstime * 1.20) : null;

        return [
            'disponible'           => true,
            'ville'                => $ville,
            'annee'                => $annee,
            'nb_pieces'            => $nbPieces,
            'loyer_m2_median'      => $lm2,
            'loyer_median'         => $lmed,
            'loyer_estime'         => $loyerEstime,
            'loyer_meuble_estime'  => $loyerMeuble,
            'nb_obs'               => $rows->sum('nb_obs'),
            'source'               => 'OLL',
            'secteurs'             => $rows->pluck('secteur')->unique()->values()->toArray(),
        ];
    }

    /**
     * Stats globales sur la table loyer_references.
     */
    public function stats(): array
    {
        $total = LoyerReference::count();

        if ($total === 0) {
            return [
                'total'       => 0,
                'annees'      => [],
                'nb_secteurs' => 0,
                'derniere_annee' => null,
            ];
        }

        $annees = DB::table('loyer_references')
            ->selectRaw('annee, COUNT(*) as nb, COUNT(DISTINCT secteur) as nb_secteurs')
            ->groupBy('annee')
            ->orderByDesc('annee')
            ->get()
            ->map(fn ($r) => [
                'annee'       => (int) $r->annee,
                'nb'          => (int) $r->nb,
                'nb_secteurs' => (int) $r->nb_secteurs,
            ])
            ->toArray();

        $derniereAnnee = LoyerReference::max('annee');
        $nbSecteurs    = DB::table('loyer_references')
            ->distinct('secteur')
            ->count('secteur');

        return [
            'total'          => $total,
            'annees'         => $annees,
            'nb_secteurs'    => $nbSecteurs,
            'derniere_annee' => $derniereAnnee,
        ];
    }
}
