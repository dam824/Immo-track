<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Simulation;
use App\Services\CashflowCalculatorService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SimulationController extends Controller
{
    public function index()
    {
        return Inertia::render('Simulations/Index', [
            'simulations' => Simulation::with(['achat', 'location'])->latest()->get(),
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('Simulations/Create', [
            'achats'             => Listing::achats()->whereNotNull('prix_achat')
                ->get(['id', 'titre', 'ville', 'superficie', 'prix_achat', 'charges_copro', 'taxe_fonciere']),
            'locations'          => Listing::locations()->whereNotNull('loyer_mensuel')
                ->get(['id', 'titre', 'ville', 'superficie', 'loyer_mensuel']),
            'preselect_achat_id' => $request->query('achat_id'),
        ]);
    }

    public function store(Request $request, CashflowCalculatorService $calc)
    {
        $data = $request->validate([
            'titre'               => 'nullable|string|max:255',
            'listing_achat_id'    => 'nullable|exists:listings,id',
            'listing_location_id' => 'nullable|exists:listings,id',
            'montant_emprunt'     => 'required|numeric|min:0',
            'taux_interet'        => 'required|numeric|min:0|max:20',
            'duree_ans'           => 'required|integer|min:1|max:30',
            'charges_copro'       => 'nullable|numeric|min:0',
            'taxe_fonciere'       => 'nullable|numeric|min:0',
            'assurance_pno'       => 'nullable|numeric|min:0',
            'loyer_retenu'        => 'required|numeric|min:0',
            'prix_achat'          => 'nullable|numeric|min:0',
            'notes'               => 'nullable|string',
        ]);

        $result = $calc->calculer([
            'montant_emprunt' => $data['montant_emprunt'],
            'taux_interet'    => $data['taux_interet'],
            'duree_ans'       => $data['duree_ans'],
            'charges_copro'   => $data['charges_copro'] ?? 0,
            'taxe_fonciere'   => $data['taxe_fonciere'] ?? 0,
            'assurance_pno'   => $data['assurance_pno'] ?? 0,
            'loyer_retenu'    => $data['loyer_retenu'],
            'prix_achat'      => $data['prix_achat'] ?? 0,
        ]);

        Simulation::create([
            'titre'               => $data['titre'] ?? null,
            'listing_achat_id'    => $data['listing_achat_id'] ?? null,
            'listing_location_id' => $data['listing_location_id'] ?? null,
            'montant_emprunt'     => $data['montant_emprunt'],
            'taux_interet'        => $data['taux_interet'],
            'duree_ans'           => $data['duree_ans'],
            'mensualite_credit'   => $result['mensualite_credit'],
            'loyer_retenu'        => $data['loyer_retenu'],
            'charges_copro'       => $data['charges_copro'] ?? 0,
            'taxe_fonciere_mois'  => ($data['taxe_fonciere'] ?? 0) / 12,
            'assurance_pno'       => $data['assurance_pno'] ?? 0,
            'cashflow_mensuel'    => $result['cashflow_mensuel'],
            'rendement_brut'      => $result['rendement_brut'],
            'rendement_net'       => $result['rendement_net'],
            'notes'               => $data['notes'] ?? null,
        ]);

        return redirect()->route('simulations.index')->with('success', 'Simulation sauvegardée.');
    }

    public function calculer(Request $request, CashflowCalculatorService $calc)
    {
        $data = $request->validate([
            'montant_emprunt' => 'required|numeric|min:0',
            'taux_interet'    => 'required|numeric|min:0|max:20',
            'duree_ans'       => 'required|integer|min:1|max:30',
            'charges_copro'   => 'nullable|numeric|min:0',
            'taxe_fonciere'   => 'nullable|numeric|min:0',
            'assurance_pno'   => 'nullable|numeric|min:0',
            'loyer_retenu'    => 'required|numeric|min:0',
            'prix_achat'      => 'nullable|numeric|min:0',
        ]);

        return response()->json($calc->calculer($data));
    }

    public function destroy(Simulation $simulation)
    {
        $simulation->delete();
        return redirect()->route('simulations.index')->with('success', 'Simulation supprimée.');
    }
}
