<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Simulation extends Model
{
    protected $fillable = [
        'titre', 'listing_achat_id', 'listing_location_id',
        'montant_emprunt', 'taux_interet', 'duree_ans',
        'mensualite_credit', 'loyer_retenu',
        'charges_copro', 'taxe_fonciere_mois', 'assurance_pno',
        'cashflow_mensuel', 'rendement_brut', 'rendement_net', 'notes',
    ];

    protected $casts = [
        'montant_emprunt'    => 'float',
        'taux_interet'       => 'float',
        'mensualite_credit'  => 'float',
        'loyer_retenu'       => 'float',
        'charges_copro'      => 'float',
        'taxe_fonciere_mois' => 'float',
        'assurance_pno'      => 'float',
        'cashflow_mensuel'   => 'float',
        'rendement_brut'     => 'float',
        'rendement_net'      => 'float',
    ];

    public function achat()
    {
        return $this->belongsTo(Listing::class, 'listing_achat_id');
    }

    public function location()
    {
        return $this->belongsTo(Listing::class, 'listing_location_id');
    }
}
