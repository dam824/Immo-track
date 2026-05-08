<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DvfTransaction extends Model
{
    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'code_commune',
        'nom_commune',
        'date_mutation',
        'valeur_fonciere',
        'superficie',
        'nb_pieces',
        'prix_m2',
    ];

    protected $casts = [
        'date_mutation'   => 'date',
        'valeur_fonciere' => 'float',
        'superficie'      => 'float',
        'prix_m2'         => 'float',
        'nb_pieces'       => 'integer',
    ];
}
