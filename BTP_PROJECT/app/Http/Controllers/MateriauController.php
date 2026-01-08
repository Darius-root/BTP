<?php

namespace App\Http\Controllers;

use App\Models\Materiau;
use App\Models\UniteMesure;
use App\Services\OrganisationContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Throwable;

class MateriauController extends Controller
{
    /**
     * Afficher la liste des matériaux
     */
    public function index()
    {
        try {
            $activeOrg = getPermissionsTeamId();
            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'SYSTEM_MATERIAU_VIEW')) {
                return back()->with('error', "Vous n'avez pas la permission de voir les matériaux.");
            }

            $materiaux = Materiau::with('unite')
                ->orderBy('code')
                ->get();

            return Inertia::render('Materiaux/Index', [
                'materiaux' => $materiaux,
            ]);

        } catch (Throwable $e) {
            Log::error('Erreur index matériaux: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors du chargement des matériaux.');
        }
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        try {
            $activeOrg = getPermissionsTeamId();
            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'SYSTEM_MATERIAU_CREATE')) {
                return back()->with('error', "Vous n'avez pas la permission de créer un matériau.");
            }

            $unites = UniteMesure::orderBy('libelle')->get();

            return Inertia::render('Materiaux/Create', [
                'unites' => $unites,
            ]);

        } catch (Throwable $e) {
            Log::error('Erreur create matériau: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de l\'accès au formulaire.');
        }
    }

    /**
     * Enregistrer un nouveau matériau
     */
    public function store(Request $request)
    {
        try {
            $activeOrg = getPermissionsTeamId();
            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'SYSTEM_MATERIAU_CREATE')) {
                return back()->with('error', "Vous n'avez pas la permission de créer un matériau.");
            }

            $validated = $request->validate([
                'code' => 'required|string|max:10|unique:materiaux',
                'nom' => 'required|string|max:255|unique:materiaux',
                'unite_id' => 'required|exists:unites_mesure,id',
            ]);

            DB::beginTransaction();

            Materiau::create($validated);

            DB::commit();

            return redirect()->route('materiaux.index')
                ->with('success', 'Matériau créé avec succès.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withInput()
                ->withErrors($e->errors());

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Erreur store matériau: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création du matériau.');
        }
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit($id)
    {
        try {
            $activeOrg = getPermissionsTeamId();
            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'SYSTEM_MATERIAU_EDIT')) {
                return back()->with('error', "Vous n'avez pas la permission de modifier ce matériau.");
            }

            $materiau = Materiau::with('unite')->findOrFail($id);
            $unites = UniteMesure::orderBy('libelle')->get();

            return Inertia::render('Materiaux/Edit', [
                'materiau' => $materiau,
                'unites' => $unites,
            ]);

        } catch (Throwable $e) {
            Log::error('Erreur edit matériau: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de l\'accès au matériau.');
        }
    }

    /**
     * Mettre à jour un matériau
     */
    public function update(Request $request, $id)
    {
        try {
            $activeOrg = getPermissionsTeamId();
            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'SYSTEM_MATERIAU_EDIT')) {
                return back()->with('error', "Vous n'avez pas la permission de modifier ce matériau.");
            }

            $materiau = Materiau::findOrFail($id);

            $validated = $request->validate([
                'code' => 'required|string|max:10|unique:materiaux,code,' . $materiau->id,
                'nom' => 'required|string|max:255|unique:materiaux,nom,' . $materiau->id,
                'unite_id' => 'required|exists:unites_mesure,id',
            ]);

            $materiau->update($validated);

            return redirect()->route('materiaux.index')
                ->with('success', 'Matériau mis à jour avec succès.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withInput()
                ->withErrors($e->errors());

        } catch (Throwable $e) {
            Log::error('Erreur update matériau: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour du matériau.');
        }
    }

    /**
     * Supprimer un matériau
     */
    public function destroy($id)
    {
        try {
            $activeOrg = getPermissionsTeamId();
            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'SYSTEM_MATERIAU_DELETE')) {
                return back()->with('error', "Vous n'avez pas la permission de supprimer ce matériau.");
            }

            $materiau = Materiau::findOrFail($id);

            // Vérifier si le matériau est utilisé dans des collections de prix
            if ($materiau->collectionsPrix()->exists()) {
                return back()->with('error', 'Impossible de supprimer ce matériau car il est utilisé dans des collections de prix.');
            }

            $materiau->delete();

            return redirect()->route('materiaux.index')
                ->with('success', 'Matériau supprimé avec succès.');

        } catch (Throwable $e) {
            Log::error('Erreur destroy matériau: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la suppression du matériau.');
        }
    }
}
