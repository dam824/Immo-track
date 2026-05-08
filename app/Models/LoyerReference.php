<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyerReference extends Model
{
    protected $fillable = [
        'annee',
        'secteur',
        'ville_normalized',
        'typo_logt',
        'nb_pieces',
        'loyer_m2_median',
        'loyer_median',
        'nb_obs',
    ];

    protected $casts = [
        'annee'          => 'integer',
        'nb_pieces'      => 'integer',
        'loyer_m2_median'=> 'float',
        'loyer_median'   => 'float',
        'nb_obs'         => 'integer',
    ];

    /**
     * Normalise un nom de ville pour la recherche.
     */
    public static function normalizeVille(string $secteur): string
    {
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT', trim($secteur));
        return strtolower((string) $ascii);
    }
}
