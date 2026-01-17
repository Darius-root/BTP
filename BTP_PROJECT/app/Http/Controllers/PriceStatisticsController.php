<?php

namespace App\Http\Controllers;

use App\Models\Arrondissement;
use App\Models\CollectionPrix;
use App\Models\Commune;
use App\Models\CorpsEtat;
use App\Models\Devise;
use App\Models\Materiau;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;


class PriceStatisticsController extends Controller
{
    public function index()
    {
        // Données pour les filtres
        $communes = Commune::all();
        $arrondissements = Arrondissement::all();
        $categories = CorpsEtat::all();
        $materiaux = Materiau::all();
        $devises = Devise::all();
        $users = User::all();

        return Inertia::render('Statistics/Index', [
            'communes' => $communes,
            'arrondissements' => $arrondissements,
            'categories' => $categories,
            'materiaux' => $materiaux,
            'devises' => $devises,
            'users' => $users,
        ]);
    }

    public function filter(Request $request)
    {
        // On construit la query en fonction des filtres
        $query = CollectionPrix::query();

        if ($request->commune_ids) {
            $query->whereIn('commune_id', $request->commune_ids);
        }
        if ($request->arrondissement_ids) {
            $query->whereIn('arrondissement_id', $request->arrondissement_ids);
        }
        if ($request->quartier) {
            $query->where('quartier', 'like', "%{$request->quartier}%");
        }
        if ($request->categorie_ids) {
            $query->whereIn('categorie_id', $request->categorie_ids);
        }
        if ($request->materiau_ids) {
            $query->whereIn('materiau_id', $request->materiau_ids);
        }
        if ($request->devise_id) {
            $query->where('devise_id', $request->devise_id);
        }
        if ($request->user_ids) {
            $query->whereIn('user_id', $request->user_ids);
        }
        if ($request->point_vente) {
            $query->where('point_vente', 'like', "%{$request->point_vente}%");
        }
        if (!is_null($request->status)) {
            $query->where('status', $request->status);
        }
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Grouper et agréger par prix
        $data = $query->with(['materiau', 'categorie', 'commune', 'arrondissement', 'devise'])
            ->get();

        return response()->json($data);
    }

}
