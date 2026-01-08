<?php

namespace App\Http\Controllers;

use App\Models\UniteMesure;
use App\Services\OrganisationContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Throwable;

class UniteMesureController extends Controller
{
    /**
     * Afficher la liste des unités de mesure
     */
    public function index()
    {
        try {
            $activeOrg = getPermissionsTeamId();
            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'SYSTEM_UNITE_MESURE_VIEW')) {
                return back()->with('error', "Vous n'avez pas la permission de voir les unités de mesure.");
            }

            $unites = UniteMesure::orderBy('code')->get();

            return Inertia::render('UnitesMesure/Index', [
                'unites' => $unites,
            ]);

        } catch (Throwable $e) {
            Log::error('Erreur index unités de mesure: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors du chargement des unités de mesure.');
        }
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        try {
            $activeOrg = getPermissionsTeamId();
            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'SYSTEM_UNITE_MESURE_CREATE')) {
                return back()->with('error', "Vous n'avez pas la permission de créer une unité de mesure.");
            }

            return Inertia::render('UnitesMesure/Create');

        } catch (Throwable $e) {
            Log::error('Erreur create unité de mesure: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de l\'accès au formulaire.');
        }
    }

    /**
     * Enregistrer une nouvelle unité de mesure
     */
    public function store(Request $request)
    {
        try {
            $activeOrg = getPermissionsTeamId();
            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'SYSTEM_UNITE_MESURE_CREATE')) {
                return back()->with('error', "Vous n'avez pas la permission de créer une unité de mesure.");
            }

            $validated = $request->validate([
                'code' => 'required|string|max:10|unique:unites_mesure',
                'libelle' => 'required|string|max:255|unique:unites_mesure',
            ]);

            DB::beginTransaction();

            UniteMesure::create($validated);

            DB::commit();

            return redirect()->route('unites-mesure.index')
                ->with('success', 'Unité de mesure créée avec succès.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withInput()
                ->withErrors($e->errors());

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Erreur store unité de mesure: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création de l\'unité de mesure.');
        }
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(UniteMesure $uniteMesure)
    {
        try {
            $activeOrg = getPermissionsTeamId();
            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'SYSTEM_UNITE_MESURE_EDIT')) {
                return back()->with('error', "Vous n'avez pas la permission de modifier cette unité de mesure.");
            }

            return Inertia::render('UnitesMesure/Edit', [
                'unite' => $uniteMesure,
            ]);

        } catch (Throwable $e) {
            Log::error('Erreur edit unité de mesure: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de l\'accès à l\'unité de mesure.');
        }
    }

    /**
     * Mettre à jour une unité de mesure
     */
    public function update(Request $request, UniteMesure $uniteMesure)
    {
        try {
            $activeOrg = getPermissionsTeamId();
            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'SYSTEM_UNITE_MESURE_EDIT')) {
                return back()->with('error', "Vous n'avez pas la permission de modifier cette unité de mesure.");
            }

            $validated = $request->validate([
                'code' => 'required|string|max:10|unique:unites_mesure,code,' . $uniteMesure->id,
                'libelle' => 'required|string|max:255|unique:unites_mesure,libelle,' . $uniteMesure->id,
            ]);

            $uniteMesure->update($validated);

            return redirect()->route('unites-mesure.index')
                ->with('success', 'Unité de mesure mise à jour avec succès.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withInput()
                ->withErrors($e->errors());

        } catch (Throwable $e) {
            Log::error('Erreur update unité de mesure: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour de l\'unité de mesure.');
        }
    }

    /**
     * Supprimer une unité de mesure
     */
    public function destroy(UniteMesure $uniteMesure)
    {
        try {
            $activeOrg = getPermissionsTeamId();
            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'SYSTEM_UNITE_MESURE_DELETE')) {
                return back()->with('error', "Vous n'avez pas la permission de supprimer cette unité de mesure.");
            }

            // Vérifier si l'unité de mesure est utilisée dans des matériaux
            if ($uniteMesure->materiaux()->exists()) {
                return back()->with('error', 'Impossible de supprimer cette unité de mesure car elle est utilisée dans des matériaux.');
            }

            $uniteMesure->delete();

            return redirect()->route('unites-mesure.index')
                ->with('success', 'Unité de mesure supprimée avec succès.');

        } catch (Throwable $e) {
            Log::error('Erreur destroy unité de mesure: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la suppression de l\'unité de mesure.');
        }
    }
}
