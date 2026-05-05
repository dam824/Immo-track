<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\Simulation;

class StatsService
{
    public function global(): array
    {
        return [
            'total_achats'      => Listing::achats()->count(),
            'total_locations'   => Listing::locations()->count(),
            'total_simulations' => Simulation::count(),
            'rendement_moyen'   => round((float) Simulation::avg('rendement_brut'), 2),
            'meilleur_cashflow' => round((float) Simulation::max('cashflow_mensuel'), 2),
        ];
    }

    public function parVille(): array
    {
        $achats = Listing::achats()
            ->whereNotNull('ville')
            ->selectRaw('ville, COUNT(*) as nb_achats, AVG(prix_m2) as prix_m2_moyen, AVG(prix_achat) as prix_moyen')
            ->groupBy('ville')
            ->get()
            ->keyBy('ville');

        $locations = Listing::locations()
            ->whereNotNull('ville')
            ->selectRaw('ville, COUNT(*) as nb_locations, AVG(loyer_m2) as loyer_m2_moyen, AVG(loyer_mensuel) as loyer_moyen')
            ->groupBy('ville')
            ->get()
            ->keyBy('ville');

        $villes = $achats->keys()->merge($locations->keys())->unique();

        return $villes->map(function ($ville) use ($achats, $locations) {
            $a = $achats->get($ville);
            $l = $locations->get($ville);
            $rendement = 0.0;
            if ($a && $l && $a->prix_moyen > 0) {
                $rendement = round(($l->loyer_moyen * 12 / $a->prix_moyen) * 100, 2);
            }
            return [
                'ville'            => $ville,
                'nb_achats'        => (int) ($a->nb_achats ?? 0),
                'prix_m2_moyen'    => $a ? (int) round($a->prix_m2_moyen) : null,
                'prix_moyen'       => $a ? (int) round($a->prix_moyen) : null,
                'nb_locations'     => (int) ($l->nb_locations ?? 0),
                'loyer_m2_moyen'   => $l ? round($l->loyer_m2_moyen, 2) : null,
                'loyer_moyen'      => $l ? (int) round($l->loyer_moyen) : null,
                'rendement_estime' => $rendement,
            ];
        })->values()->toArray();
    }
}
