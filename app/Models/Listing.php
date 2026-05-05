<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    protected $fillable = [
        'type', 'titre', 'url', 'thumbnail_url', 'source',
        'departement', 'code_postal', 'ville', 'quartier',
        'superficie', 'nb_pieces', 'nb_chambres', 'dpe', 'meuble',
        'prix_achat', 'prix_m2', 'loyer_mensuel', 'loyer_m2', 'charges_incluses',
        'charges_copro', 'taxe_fonciere', 'travaux_estimes',
        'statut', 'notes',
    ];

    protected $casts = [
        'meuble'           => 'boolean',
        'charges_incluses' => 'boolean',
        'prix_achat'       => 'float',
        'loyer_mensuel'    => 'float',
        'superficie'       => 'float',
        'prix_m2'          => 'float',
        'loyer_m2'         => 'float',
        'charges_copro'    => 'float',
        'taxe_fonciere'    => 'float',
        'travaux_estimes'  => 'float',
    ];

    protected static function booted(): void
    {
        static::saving(function (Listing $listing) {
            if ($listing->superficie > 0) {
                if ($listing->prix_achat) {
                    $listing->prix_m2 = round($listing->prix_achat / $listing->superficie, 2);
                }
                if ($listing->loyer_mensuel) {
                    $listing->loyer_m2 = round($listing->loyer_mensuel / $listing->superficie, 2);
                }
            }
        });
    }

    public function scopeAchats($query)
    {
        return $query->where('type', 'achat');
    }

    public function scopeLocations($query)
    {
        return $query->where('type', 'location');
    }

    public function scopeVille($query, string $ville)
    {
        return $query->where('ville', $ville);
    }

    public function simulations()
    {
        return $this->hasMany(Simulation::class, 'listing_achat_id');
    }
}
