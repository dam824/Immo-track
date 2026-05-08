<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ListingController extends Controller
{
    public function indexAchats()
    {
        return Inertia::render('Achats/Index', [
            'listings' => Listing::achats()->latest()->get(),
        ]);
    }

    public function indexLocations()
    {
        return Inertia::render('Locations/Index', [
            'listings' => Listing::locations()->latest()->get(),
        ]);
    }

    public function createAchat()
    {
        return Inertia::render('Achats/Create');
    }

    public function createLocation()
    {
        return Inertia::render('Locations/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type'             => 'required|in:achat,location',
            'titre'            => 'nullable|string|max:255',
            'url'              => 'nullable|string|max:2048',
            'thumbnail_url'    => 'nullable|string|max:2048',
            'source'           => 'nullable|string|max:100',
            'departement'      => 'nullable|string|max:3',
            'code_postal'      => 'nullable|string|max:10',
            'ville'            => 'nullable|string|max:100',
            'quartier'         => 'nullable|string|max:100',
            'superficie'       => 'nullable|numeric|min:0',
            'nb_pieces'        => 'nullable|integer|min:0',
            'nb_chambres'      => 'nullable|integer|min:0',
            'dpe'              => 'nullable|string|max:1',
            'meuble'           => 'nullable|boolean',
            'prix_achat'       => 'nullable|numeric|min:0',
            'loyer_mensuel'    => 'nullable|numeric|min:0',
            'charges_incluses'  => 'nullable|boolean',
            'charges_locatives' => 'nullable|numeric|min:0',
            'charges_copro'     => 'nullable|numeric|min:0',
            'taxe_fonciere'             => 'nullable|numeric|min:0',
            'travaux_estimes'           => 'nullable|numeric|min:0',
            'charges_annuelles_energie' => 'nullable|numeric|min:0',
            'cave'                      => 'nullable|boolean',
            'parking'                   => 'nullable|boolean',
            'balcon'                    => 'nullable|boolean',
            'ascenseur'                 => 'nullable|boolean',
            'gardien'                   => 'nullable|boolean',
            'digicode'                  => 'nullable|boolean',
            'statut'                    => 'nullable|string',
            'notes'                     => 'nullable|string',
        ]);

        $listing = Listing::create($data);

        if ($data['type'] === 'achat') {
            return redirect()->route('achats.show', $listing)->with('success', 'Annonce ajoutée.');
        }
        return redirect()->route('locations.index')->with('success', 'Location ajoutée.');
    }

    public function showAchat(Listing $listing)
    {
        $locsMemeVille = $listing->ville
            ? Listing::locations()->ville($listing->ville)->get()
            : collect();

        return Inertia::render('Achats/Show', [
            'listing'              => $listing,
            'simulations'          => $listing->simulations()->with('location')->latest()->get(),
            'locations_meme_ville' => $locsMemeVille,
        ]);
    }

    public function editAchat(Listing $listing)
    {
        return Inertia::render('Achats/Edit', ['listing' => $listing]);
    }

    public function editLocation(Listing $listing)
    {
        return Inertia::render('Locations/Edit', ['listing' => $listing]);
    }

    public function update(Request $request, Listing $listing)
    {
        $data = $request->validate([
            'titre'            => 'nullable|string|max:255',
            'url'              => 'nullable|string|max:2048',
            'thumbnail_url'    => 'nullable|string|max:2048',
            'source'           => 'nullable|string|max:100',
            'departement'      => 'nullable|string|max:3',
            'code_postal'      => 'nullable|string|max:10',
            'ville'            => 'nullable|string|max:100',
            'quartier'         => 'nullable|string|max:100',
            'superficie'       => 'nullable|numeric|min:0',
            'nb_pieces'        => 'nullable|integer|min:0',
            'nb_chambres'      => 'nullable|integer|min:0',
            'dpe'              => 'nullable|string|max:1',
            'meuble'           => 'nullable|boolean',
            'prix_achat'       => 'nullable|numeric|min:0',
            'loyer_mensuel'    => 'nullable|numeric|min:0',
            'charges_incluses'  => 'nullable|boolean',
            'charges_locatives' => 'nullable|numeric|min:0',
            'charges_copro'     => 'nullable|numeric|min:0',
            'taxe_fonciere'             => 'nullable|numeric|min:0',
            'travaux_estimes'           => 'nullable|numeric|min:0',
            'charges_annuelles_energie' => 'nullable|numeric|min:0',
            'cave'                      => 'nullable|boolean',
            'parking'                   => 'nullable|boolean',
            'balcon'                    => 'nullable|boolean',
            'ascenseur'                 => 'nullable|boolean',
            'gardien'                   => 'nullable|boolean',
            'digicode'                  => 'nullable|boolean',
            'statut'                    => 'nullable|string',
            'notes'                     => 'nullable|string',
        ]);

        $listing->update($data);

        if ($listing->type === 'achat') {
            return redirect()->route('achats.show', $listing)->with('success', 'Mise à jour effectuée.');
        }
        return redirect()->route('locations.index')->with('success', 'Mise à jour effectuée.');
    }

    public function destroy(Listing $listing)
    {
        $type = $listing->type;
        $listing->delete();
        return redirect()->route($type === 'achat' ? 'achats.index' : 'locations.index')
            ->with('success', 'Annonce supprimée.');
    }

    public function parVille(string $ville)
    {
        return response()->json(
            Listing::locations()->ville($ville)->get()
        );
    }
}
