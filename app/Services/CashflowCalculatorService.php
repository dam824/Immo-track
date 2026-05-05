<?php

namespace App\Services;

class CashflowCalculatorService
{
    public function mensualite(float $montant, float $taux, int $dureeAns): float
    {
        if ($montant <= 0) return 0.0;
        $r = ($taux / 100) / 12;
        $n = $dureeAns * 12;
        if ($r == 0) return round($montant / $n, 2);
        return round($montant * ($r * pow(1 + $r, $n)) / (pow(1 + $r, $n) - 1), 2);
    }

    public function calculer(array $p): array
    {
        $montant   = (float) ($p['montant_emprunt'] ?? 0);
        $taux      = (float) ($p['taux_interet'] ?? 0);
        $duree     = (int)   ($p['duree_ans'] ?? 20);
        $copro     = (float) ($p['charges_copro'] ?? 0);
        $tf        = (float) ($p['taxe_fonciere'] ?? 0);
        $assurance = (float) ($p['assurance_pno'] ?? 0);
        $loyer     = (float) ($p['loyer_retenu'] ?? 0);
        $prixAchat = (float) ($p['prix_achat'] ?? 0);

        $mensualite = $this->mensualite($montant, $taux, $duree);
        $tfMois     = $tf / 12;
        $sorties    = $mensualite + $copro + $tfMois + $assurance;
        $cashflow   = $loyer - $sorties;

        $rendBrut = $prixAchat > 0
            ? round(($loyer * 12 / $prixAchat) * 100, 2)
            : 0.0;

        $netBase = $loyer - $copro - $tfMois - $assurance;
        $rendNet = $prixAchat > 0
            ? round(($netBase * 12 / $prixAchat) * 100, 2)
            : 0.0;

        return [
            'mensualite_credit' => $mensualite,
            'sorties_totales'   => round($sorties, 2),
            'cashflow_mensuel'  => round($cashflow, 2),
            'rendement_brut'    => $rendBrut,
            'rendement_net'     => $rendNet,
        ];
    }
}
